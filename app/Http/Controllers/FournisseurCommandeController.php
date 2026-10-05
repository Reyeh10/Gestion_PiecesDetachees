<?php

namespace App\Http\Controllers;

use App\Models\ExternalBonCommande;
use App\Models\ExternalBonCommandeLigne;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Services\AppAtelierApiService;
use App\Services\BonTransfertService;

use Illuminate\Http\Request;

/**
 * Affiche les bons de commande reçus en temps réel depuis app-atelier
 * (le garage), avec la disponibilité déjà calculée à la réception.
 *
 * Certaines lignes arrivent sans référence pièce (le garage ne la connaît
 * pas toujours — main d'œuvre, peinture...) : le vendeur peut alors
 * retrouver lui-même la pièce correspondante (marque/modèle + désignation)
 * et indiquer manuellement si elle est disponible.
 */
class FournisseurCommandeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $dispo = $request->get('dispo', '');

        $commandes = ExternalBonCommande::withCount('lignes')
            ->with(['lignes' => fn ($q) => $q->orderBy('position'), 'bonTransfert.lignes'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhere('client_nom', 'like', "%{$search}%")
                      ->orWhere('vehicule_marque', 'like', "%{$search}%")
                      ->orWhere('vehicule_modele', 'like', "%{$search}%")
                      ->orWhere('vehicule_immatriculation', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        if ($dispo !== '') {
            $commandes = $commandes->filter(function ($commande) use ($dispo) {
                return $this->categorieDisponibilite($commande) === $dispo;
            })->values();
        }

        return view('fournisseur-commandes.index', [
            'commandes' => $commandes,
            'search'    => $search,
            'dispo'     => $dispo,
        ]);
    }

    /**
     * Catégorise la disponibilité globale d'un bon de commande, pour
     * l'affichage et le filtre : en_attente / tout / rien / partiel.
     */
    private function categorieDisponibilite(ExternalBonCommande $commande): string
    {
        $total = $commande->lignes->count();
        $repondues = $commande->lignes->whereNotNull('disponible')->count();
        $disponibles = $commande->lignes->where('disponible', true)->count();

        if ($total === 0 || $repondues === 0) return 'en_attente';
        if ($disponibles === $total) return 'tout';
        if ($disponibles === 0) return 'rien';
        return 'partiel';
    }

    public function show(ExternalBonCommande $fournisseurCommande)
    {
        if (! $fournisseurCommande->vu_at) {
            $fournisseurCommande->update(['vu_at' => now()]);
        }

        $fournisseurCommande->load([
            'lignes' => fn ($q) => $q->orderBy('position'),
            'lignes.product.depotStocks' => fn ($q) => $q->where('quantity', '>', 0)->orderByDesc('quantity'),
            'lignes.product.depotStocks.depot',
            'lignes.depot',
            'bonTransfert.lignes',
        ]);

        $products = Product::with([
            'brand',
            'model',
            'depotStocks' => fn ($q) => $q->where('quantity', '>', 0)->orderByDesc('quantity'),
            'depotStocks.depot',
        ])
            ->orderBy('designation')
            ->get();

        return view('fournisseur-commandes.show', [
            'commande' => $fournisseurCommande,
            'products' => $products,
        ]);
    }

    /**
     * Le vendeur associe une ligne sans référence à une pièce trouvée dans
     * le stock (ou la marque manuellement indisponible s'il n'a rien trouvé).
     *
     * Une ligne reste modifiable (autre pièce, autre dépôt), même après la
     * création du bon de transfert : le BT est alors signalé « à mettre à
     * jour ». Seuls les anciens BC facturés (vente) sont verrouillés.
     */
    public function updateLigne(Request $request, ExternalBonCommande $fournisseurCommande, ExternalBonCommandeLigne $ligne)
    {
        abort_unless($ligne->external_bon_commande_id === $fournisseurCommande->id, 404);

        if ($fournisseurCommande->estFacture()) {
            return back()->with('error', 'Ce bon a déjà été facturé (ancienne vente) : les lignes ne sont plus modifiables.');
        }

        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'depot_id'   => 'nullable|exists:depots,id',
            'note'       => 'nullable|string|max:255',
        ], [
            'note.max' => 'La note ne doit pas dépasser 255 caractères.',
        ]);

        [$ok, $message] = $this->appliquerLigne($ligne, $data, $request->has('note'));

        return back()->with($ok ? 'success' : 'error', $message);
    }

    /**
     * Valide en une seule fois toutes les lignes du bon de commande
     * (pièce + dépôt + note de chaque ligne), au lieu d'une ligne à la fois.
     * Les lignes intactes (rien de choisi, rien de changé) sont ignorées.
     */
    public function updateLignes(Request $request, ExternalBonCommande $fournisseurCommande)
    {
        if ($fournisseurCommande->estFacture()) {
            return back()->with('error', 'Ce bon a déjà été facturé (ancienne vente) : les lignes ne sont plus modifiables.');
        }

        $request->validate([
            'lignes'              => 'nullable|array',
            'lignes.*.product_id' => 'nullable|exists:products,id',
            'lignes.*.depot_id'   => 'nullable|exists:depots,id',
            'lignes.*.note'       => 'nullable|string|max:255',
        ], [
            'lignes.*.note.max' => 'Une note ne doit pas dépasser 255 caractères.',
        ]);

        $saisies = (array) $request->input('lignes', []);
        $misesAJour = 0;
        $erreurs = [];

        foreach ($fournisseurCommande->lignes()->orderBy('position')->get() as $ligne) {
            if (! isset($saisies[$ligne->id])) {
                continue;
            }

            $saisie = $saisies[$ligne->id];
            $productId = ! empty($saisie['product_id']) ? (int) $saisie['product_id'] : null;
            $depotId = ! empty($saisie['depot_id']) ? (int) $saisie['depot_id'] : null;
            $note = isset($saisie['note']) && trim((string) $saisie['note']) !== '' ? trim((string) $saisie['note']) : null;

            $productActuel = $ligne->product_id ? (int) $ligne->product_id : null;
            $depotActuel = $ligne->depot_id ? (int) $ligne->depot_id : null;

            // Ligne restée en attente : rien choisi, rien saisi -> on n'y touche pas.
            if (! $productId && ! $productActuel && $note === null && is_null($ligne->disponible)) {
                continue;
            }

            // Ligne inchangée -> inutile de renvoyer quoi que ce soit au garage.
            $produitInchange = ($productId ?? $productActuel) === $productActuel;
            if ($produitInchange && $depotId === $depotActuel && $note === ($ligne->note ?: null)) {
                continue;
            }

            [$ok, $message] = $this->appliquerLigne(
                $ligne,
                ['product_id' => $productId, 'depot_id' => $depotId, 'note' => $note],
                true
            );

            if ($ok) {
                $misesAJour++;
            } else {
                $erreurs[] = ($ligne->designation ?: $ligne->reference ?: "ligne #{$ligne->position}").' : '.$message;
            }
        }

        if ($erreurs) {
            $retour = back()->with('error', implode(' | ', $erreurs));

            return $misesAJour
                ? $retour->with('success', "{$misesAJour} ligne(s) validée(s).")
                : $retour;
        }

        return back()->with('success', $misesAJour
            ? "{$misesAJour} ligne(s) validée(s) et transmise(s) au garage."
            : 'Aucune modification à enregistrer.');
    }

    /**
     * Applique le choix (pièce/dépôt/note) sur une ligne.
     *
     * @return array{0:bool,1:string} [succès, message]
     */
    private function appliquerLigne(ExternalBonCommandeLigne $ligne, array $data, bool $noteFournie): array
    {
        // Produit effectif : celui choisi dans le menu (ligne sans référence)
        // ou celui déjà identifié automatiquement (ligne avec référence, on ne
        // change alors que le dépôt).
        $productId = $data['product_id'] ?? $ligne->product_id;

        if (! $productId) {
            // Le vendeur a cherché et n'a rien trouvé de correspondant.
            $ligne->update([
                'product_id'          => null,
                'depot_id'            => null,
                'quantite_disponible' => 0,
                'disponible'          => false,
                'prix_unitaire'       => null,
                'note'                => $data['note'] ?? null,
            ]);

            app(AppAtelierApiService::class)->envoyerDisponibilite($ligne);

            return [true, 'Ligne marquée sans pièce correspondante et transmise au garage.'];
        }

        $product = Product::findOrFail($productId);
        $depotId = $data['depot_id'] ?? null;

        // Dépôts où la pièce a du stock.
        $depotStocks = ProductDepotStock::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->get();

        // Un seul dépôt en stock : on le retient sans obliger le vendeur à le choisir.
        if (! $depotId && $depotStocks->count() === 1) {
            $depotId = (int) $depotStocks->first()->depot_id;
        }

        $depotStock = $depotId
            ? $depotStocks->firstWhere('depot_id', $depotId)
            : null;

        if ($depotId && ! $depotStock) {
            return [false, "Cette pièce n'a pas de stock dans le dépôt choisi."];
        }

        $qteDepot = $depotStock ? (float) $depotStock->quantity : 0.0;
        $qteDemandee = (float) $ligne->quantite_demandee;

        $ligne->update([
            'product_id'          => $product->id,
            'depot_id'            => $depotId,
            // On NE réécrit PAS `reference` : elle reste vide pour une ligne
            // sans référence garage, ce qui garde le menu de recherche pièce
            // disponible pour corriger une identification manuelle erronée.
            'quantite_disponible' => $depotId ? $qteDepot : null,
            // Tant qu'aucun dépôt n'est choisi, la disponibilité reste indéterminée.
            'disponible'          => $depotId ? ($qteDepot >= $qteDemandee) : null,
            'prix_unitaire'       => $product->sale_price,
            'note'                => $noteFournie ? ($data['note'] ?? null) : $ligne->note,
        ]);

        if ($ligne->depot_id) {
            app(AppAtelierApiService::class)->envoyerDisponibilite($ligne);

            return [true, 'Disponibilité mise à jour et transmise au garage.'];
        }

        return [true, 'Pièce identifiée. Choisissez le dépôt de prélèvement pour finaliser.'];
    }

    /**
     * Crée le bon de transfert (BT) du bon de commande, une fois toutes les
     * pièces identifiées, rattachées à un dépôt et disponibles : les pièces
     * sortent du stock du magasin (sans TVA ni paiement), puis le BT est
     * envoyé au garage (app Atelier). Les anciens BC déjà convertis en
     * vente/facture restent tels quels.
     */
    public function creerBonTransfert(
        ExternalBonCommande $fournisseurCommande,
        BonTransfertService $service,
        AppAtelierApiService $atelier
    ) {
        if ($fournisseurCommande->bon_transfert_id) {
            return redirect()->route('bons-transfert.show', $fournisseurCommande->bon_transfert_id);
        }

        if ($fournisseurCommande->vente_id) {
            return redirect()->route('sales.show', $fournisseurCommande->vente_id);
        }

        $fournisseurCommande->load('lignes');

        $sansDepot = $fournisseurCommande->lignes
            ->filter(fn (ExternalBonCommandeLigne $ligne) => $ligne->product_id && ! $ligne->depot_id);

        if ($sansDepot->isNotEmpty()) {
            $refs = $sansDepot->map(fn ($l) => $l->reference ?: $l->designation)->implode(', ');

            return back()->with('error', "Impossible : choisissez d'abord le dépôt de prélèvement pour : {$refs}.");
        }

        if (! $fournisseurCommande->toutesPiecesDisponibles()) {
            return back()->with('error', "Impossible : toutes les pièces ne sont pas encore identifiées et disponibles pour {$fournisseurCommande->numero}.");
        }

        try {
            $bt = $service->creer($fournisseurCommande);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Envoi au garage après la création (hors transaction) : si l'Atelier
        // est injoignable, le BT reste créé et pourra être renvoyé depuis sa page.
        $erreurEnvoi = $atelier->envoyerBonTransfert($bt);

        $redirection = redirect()->route('bons-transfert.show', $bt);

        if ($erreurEnvoi) {
            return $redirection
                ->with('success', "Bon de transfert {$bt->numero} créé à partir de {$fournisseurCommande->numero}.")
                ->with('error', "Mais l'envoi au garage a échoué : {$erreurEnvoi} Utilisez « Renvoyer au garage ».");
        }

        return $redirection
            ->with('success', "Bon de transfert {$bt->numero} créé à partir de {$fournisseurCommande->numero} et envoyé au garage.");
    }

    /**
     * Met le bon de transfert à jour après une modification du BC (devis
     * modifié côté Atelier, pièce ou dépôt changé ici) : même numéro, stock
     * corrigé de la différence, puis renvoi au garage.
     */
    public function mettreAJourBonTransfert(
        ExternalBonCommande $fournisseurCommande,
        BonTransfertService $service,
        AppAtelierApiService $atelier
    ) {
        if (! $fournisseurCommande->bon_transfert_id) {
            return back()->with('error', "Aucun bon de transfert à mettre à jour pour {$fournisseurCommande->numero}.");
        }

        $fournisseurCommande->load(['lignes', 'bonTransfert.lignes']);

        if ($fournisseurCommande->bonTransfertAJour()) {
            return redirect()->route('bons-transfert.show', $fournisseurCommande->bon_transfert_id)
                ->with('success', 'Le bon de transfert est déjà à jour.');
        }

        if (! $fournisseurCommande->toutesPiecesDisponibles()) {
            return back()->with('error', "Impossible : toutes les pièces ne sont pas encore identifiées et disponibles pour {$fournisseurCommande->numero}.");
        }

        try {
            $bt = $service->mettreAJour($fournisseurCommande);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $erreurEnvoi = $atelier->envoyerBonTransfert($bt);

        $redirection = redirect()->route('bons-transfert.show', $bt);

        if ($erreurEnvoi) {
            return $redirection
                ->with('success', "Bon de transfert {$bt->numero} mis à jour.")
                ->with('error', "Mais l'envoi au garage a échoué : {$erreurEnvoi} Utilisez « Renvoyer au garage ».");
        }

        return $redirection->with('success', "Bon de transfert {$bt->numero} mis à jour et renvoyé au garage.");
    }
}
