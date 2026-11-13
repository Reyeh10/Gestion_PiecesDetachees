<?php

namespace App\Http\Controllers;

use App\Models\ExternalBonCommande;
use App\Models\ExternalBonCommandeLigne;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Services\AppAtelierApiService;
use App\Services\BonTransfertService;
use App\Models\User;

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

        /*
         * Recherche : chaque mot doit se retrouver quelque part (n° BC,
         * client, téléphone, marque, modèle, immatriculation, VIN, ou une
         * pièce du BC : référence / désignation). « Toyota Hilux » trouve
         * donc les Toyota Hilux, « 7730 » trouve par téléphone.
         */
        $mots = array_filter(preg_split('/\s+/', $search));

        $commandes = ExternalBonCommande::garage()->withCount('lignes')
            ->withCount([
                'lignes as lignes_repondues_count' => fn ($q) => $q->whereNotNull('disponible'),
                'lignes as lignes_dispo_count' => fn ($q) => $q->where('disponible', true),
            ])
            ->with(['lignes' => fn ($q) => $q->orderBy('position'), 'bonTransfert.lignes'])
            ->when($mots, function ($query) use ($mots) {
                foreach ($mots as $mot) {
                    $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $mot) . '%';
                    $query->where(function ($q) use ($like) {
                        $q->where('numero', 'like', $like)
                          ->orWhere('client_nom', 'like', $like)
                          ->orWhere('client_telephone', 'like', $like)
                          ->orWhere('vehicule_marque', 'like', $like)
                          ->orWhere('vehicule_modele', 'like', $like)
                          ->orWhere('vehicule_immatriculation', 'like', $like)
                          ->orWhere('vehicule_vin', 'like', $like)
                          ->orWhere('equipement', 'like', $like)
                          ->orWhereHas('lignes', fn ($l) => $l->where('reference', 'like', $like)->orWhere('designation', 'like', $like));
                    });
                }
            })
            // Disponibilité filtrée en base (même règle que categorieDisponibilite)
            ->when($dispo === 'en_attente', fn ($q) => $q->having('lignes_repondues_count', '=', 0))
            ->when($dispo === 'tout', fn ($q) => $q->having('lignes_repondues_count', '>', 0)->havingRaw('lignes_dispo_count = lignes_count'))
            ->when($dispo === 'rien', fn ($q) => $q->having('lignes_repondues_count', '>', 0)->having('lignes_dispo_count', '=', 0))
            ->when($dispo === 'partiel', fn ($q) => $q->having('lignes_dispo_count', '>', 0)->havingRaw('lignes_dispo_count < lignes_count'))
            ->latest()
            ->paginate($this->commandesParPage($request))
            ->withQueryString();

        return view('fournisseur-commandes.index', [
            'commandes' => $commandes,
            'search'    => $search,
            'dispo'     => $dispo,
        ]);
    }

    /*
     * Nombre de commandes par page (10, 25, 50 ou 100), mémorisé pour la
     * session, comme la liste des produits.
     */
    public const PAR_PAGE = [10, 25, 50, 100];

    private function commandesParPage(Request $request): int
    {
        $demande = (int) $request->query('par_page');

        if (in_array($demande, self::PAR_PAGE, true)) {
            session(['commandes_garage_par_page' => $demande]);

            return $demande;
        }

        $memorise = (int) session('commandes_garage_par_page', 25);

        return in_array($memorise, self::PAR_PAGE, true) ? $memorise : 25;
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
        // V6 : une sortie bus n'est pas une commande du garage -> son BT
        if ($fournisseurCommande->estBus()) {
            return $fournisseurCommande->bon_transfert_id
                ? redirect()->route('bons-transfert.show', $fournisseurCommande->bon_transfert_id)
                : redirect()->route('bus.index');
        }

        // V6 : un BT direct « + BT » non plus -> son BT
        if ($fournisseurCommande->estBtDirect()) {
            return $fournisseurCommande->bon_transfert_id
                ? redirect()->route('bons-transfert.show', $fournisseurCommande->bon_transfert_id)
                : redirect()->route('bons-transfert.index');
        }

        if (! $fournisseurCommande->vu_at) {
            $fournisseurCommande->update(['vu_at' => now()]);
        }

        $fournisseurCommande->load([
            'lignes' => fn ($q) => $q->orderBy('position'),
            'lignes.product.depotStocks' => fn ($q) => $q->where('quantity', '>', 0)->orderByDesc('quantity'),
            'lignes.product.depotStocks.depot',
            'lignes.depot',
            'bonTransfert.lignes',
            'bonsTransfert.lignes',
            'lignes.bonTransfert',
            'lignes.demandePiece.supplier',
            'lignes.demandePiece.latestSupplierOrderItem.supplierOrder',
        ]);

        $products = Product::with([
            'brand',
            'model',
            'depotStocks' => fn ($q) => $q->where('quantity', '>', 0)->orderByDesc('quantity'),
            'depotStocks.depot',
        ])
            ->orderBy('designation')
            ->get();

        // V6 — « Commander au fournisseur » : pièces non disponibles, pas
        // encore transférées ni déjà commandées (BC fournisseur non annulé).
        $aCommander = $fournisseurCommande->estFacture()
            ? collect()
            : $fournisseurCommande->lignes->filter(
                fn (ExternalBonCommandeLigne $l) => ! $l->bon_transfert_id && ! $l->estPrete() && ! $this->ligneDejaCommandee($l)
            )->values();

        $depots = \App\Models\Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('fournisseur-commandes.show', [
            'commande'       => $fournisseurCommande,
            'products'       => $products,
            'aCommander'     => $aCommander,
            'peutCommander'  => auth()->user()->hasPermission('gerer_commandes_fournisseurs'),
            'suppliers'      => \App\Models\Supplier::query()->orderBy('name')->get(['id', 'name', 'currency']),
            'depots'         => $depots,
            'depotParDefaut' => $depots->first(fn ($d) => stripos($d->name, 'guelleh') !== false)?->id ?? $depots->first()?->id,
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

        // Pièce EN RUPTURE (aucun dépôt n'en a) : elle existe au catalogue, on la
        // garde sur la ligne et on transmet au garage « indisponible » AVEC le prix
        // de vente — le devis montre le vrai montant au client. Quand la pièce
        // revient en stock, le vendeur choisit le dépôt : la ligne passe disponible.
        if (! $depotId && $depotStocks->isEmpty()) {
            $ligne->update([
                'product_id'          => $product->id,
                'depot_id'            => null,
                'quantite_disponible' => 0,
                'disponible'          => false,
                'prix_unitaire'       => $product->sale_price,
                'note'                => $noteFournie && ! empty($data['note']) ? $data['note'] : 'En rupture de stock',
            ]);

            app(AppAtelierApiService::class)->envoyerDisponibilite($ligne);

            return [true, 'Pièce en rupture : prix de vente transmis au garage (indisponible pour le moment).'];
        }

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
     * Crée le bon de transfert (BT) du bon de commande. V6 : BT PARTIEL —
     * seules les pièces prêtes (identifiées, avec dépôt, disponibles) partent ;
     * les autres restent en attente et partiront dans un « BT du reste ».
     * Les pièces sortent du stock du magasin (sans TVA ni paiement), puis le
     * BT est envoyé au garage (app Atelier).
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

        if ($fournisseurCommande->lignesPretesAEnvoyer()->isEmpty()) {
            return back()->with('error', "Impossible : aucune pièce de {$fournisseurCommande->numero} n'est encore identifiée, avec un dépôt et disponible.");
        }

        try {
            $bt = $service->creer($fournisseurCommande);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Le BC vient d'être traité : il n'est plus « nouveau » (compteur du menu).
        $fournisseurCommande->forceFill(['vu_at' => now()])->save();

        return $this->apresEnvoi($atelier, $bt, $fournisseurCommande->fresh('lignes'), "Bon de transfert {$bt->numero} créé à partir de {$fournisseurCommande->numero}");
    }

    /**
     * V6 — « BT du reste » : les pièces restées en attente et maintenant
     * prêtes (réapprovisionnées) partent dans un nouveau BT (BT-…-R1, -R2…),
     * relié au premier BT du BC.
     */
    public function creerBonTransfertReste(
        ExternalBonCommande $fournisseurCommande,
        BonTransfertService $service,
        AppAtelierApiService $atelier
    ) {
        try {
            $bt = $service->creerReste($fournisseurCommande);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $fournisseurCommande->forceFill(['vu_at' => now()])->save();

        return $this->apresEnvoi($atelier, $bt, $fournisseurCommande->fresh('lignes'), "Bon de transfert du reste {$bt->numero} créé pour {$fournisseurCommande->numero}");
    }

    /** Envoi au garage (hors transaction) puis retour sur le BT avec le bon message. */
    private function apresEnvoi(AppAtelierApiService $atelier, $bt, ExternalBonCommande $bc, string $message)
    {
        $enAttente = $bc->lignesEnAttente()->count();

        if ($enAttente > 0) {
            $message .= " — {$enAttente} pièce(s) en attente sur le BC (elles partiront dans un BT du reste)";
        }

        // Si l'Atelier est injoignable, le BT reste créé et pourra être
        // renvoyé depuis sa page.
        $erreurEnvoi = $atelier->envoyerBonTransfert($bt);

        $redirection = redirect()->route('bons-transfert.show', $bt);

        if ($erreurEnvoi) {
            return $redirection
                ->with('success', $message . '.')
                ->with('error', "Mais l'envoi au garage a échoué : {$erreurEnvoi} Utilisez « Renvoyer au garage ».");
        }

        return $redirection->with('success', $message . ', et envoyé au garage.');
    }

    /**
     * Met les bons de transfert à jour après une modification du BC (devis
     * modifié côté Atelier, pièce ou dépôt changé ici). V6 : chaque BT (le
     * premier et les BT du reste) est corrigé pour ses pièces, puis renvoyé
     * au garage. Les pièces ajoutées restent en attente (BT du reste).
     */
    public function mettreAJourBonTransfert(
        ExternalBonCommande $fournisseurCommande,
        BonTransfertService $service,
        AppAtelierApiService $atelier
    ) {
        if (! $fournisseurCommande->bon_transfert_id) {
            return back()->with('error', "Aucun bon de transfert à mettre à jour pour {$fournisseurCommande->numero}.");
        }

        $fournisseurCommande->load(['lignes', 'bonsTransfert.lignes']);

        if ($fournisseurCommande->bonTransfertAJour()) {
            return redirect()->route('bons-transfert.show', $fournisseurCommande->bon_transfert_id)
                ->with('success', 'Les bons de transfert sont déjà à jour.');
        }

        try {
            $modifies = $service->mettreAJour($fournisseurCommande);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Le BC vient d'être traité : il n'est plus « nouveau » (compteur du menu).
        $fournisseurCommande->forceFill(['vu_at' => now()])->save();

        $erreurs = [];

        foreach ($modifies as $bt) {
            if ($erreur = $atelier->envoyerBonTransfert($bt)) {
                $erreurs[] = "{$bt->numero} : {$erreur}";
            }
        }

        $numeros = collect($modifies)->pluck('numero')->implode(', ') ?: 'aucun';
        $redirection = redirect()->route('bons-transfert.show', $fournisseurCommande->fresh()->bon_transfert_id ?? $fournisseurCommande->bon_transfert_id);

        if ($erreurs) {
            return $redirection
                ->with('success', "Bons de transfert mis à jour : {$numeros}.")
                ->with('error', "Mais l'envoi au garage a échoué pour " . implode(' ; ', $erreurs) . ' Utilisez « Renvoyer au garage ».');
        }

        return $redirection->with('success', "Bons de transfert mis à jour et renvoyés au garage : {$numeros}.");
    }

    /**
     * Suppression manuelle d'un bon de commande reçu du garage —
     * administrateur uniquement (par exemple devis supprimé côté garage).
     * Son bon de transfert éventuel est supprimé avec lui (case « pièces
     * revenues au magasin » : remise en stock). Un ancien BC facturé
     * (vente) ne se supprime pas : la facture s'annule par un avoir.
     */
    public function destroy(Request $request, ExternalBonCommande $fournisseurCommande, BonTransfertService $service)
    {
        // Récupérer l'utilisateur actuellement connecté.
    $user = auth()->user();

    // Seul un administrateur authentifié peut supprimer
    // un bon de commande provenant de l'Atelier.
    abort_unless(
        $user instanceof User && $user->isAdmin(),
        403,
        'Seul un administrateur peut supprimer un bon de commande.'
    );

        if ($fournisseurCommande->estFacture()) {
            return back()->with('error', "Ce bon de commande a été facturé : il ne se supprime pas (annulez la facture par un avoir).");
        }

        $numero = $fournisseurCommande->numero;
        $remettre = $request->boolean('remettre_en_stock');
        $numeroBt = null;

        \Illuminate\Support\Facades\DB::transaction(function () use ($fournisseurCommande, $service, $remettre, &$numeroBt) {
            if ($bt = $fournisseurCommande->bonTransfert) {
                $numeroBt = $bt->numero;
                $service->supprimer($bt, $remettre);
            }

            $fournisseurCommande->refresh()->delete();
        });

        $message = "Bon de commande {$numero} supprimé.";
        if ($numeroBt) {
            $message .= " Son bon de transfert {$numeroBt} aussi"
                . ($remettre ? ', et les pièces ont été remises dans leurs dépôts.' : ' (stock non modifié).');
        }

        return redirect()->route('fournisseur-commandes.index')->with('success', $message);
    }

    /**
     * V6 — « Commander au fournisseur » : pour les pièces NON disponibles
     * d'un BC du garage, on choisit seulement le fournisseur et le dépôt de
     * réception. Le système crée les demandes de pièces spéciales (statut
     * Commandée, véhicule du BC repris en description) et le bon de commande
     * fournisseur en brouillon (approbation de l'admin comme les autres).
     * La référence est saisie côté Magasin ; si elle n'existe pas au
     * catalogue, la fiche produit est créée à la réception.
     */
    public function commanderFournisseur(Request $request, ExternalBonCommande $fournisseurCommande)
    {
        $validated = $request->validate([
            'supplier_id'             => ['required', 'integer', 'exists:suppliers,id'],
            'depot_id'                => ['required', 'integer', 'exists:depots,id'],
            'lignes'                  => ['required', 'array'],
            'lignes.*.commander'      => ['nullable'],
            'lignes.*.reference'      => ['nullable', 'string', 'max:100'],
            'lignes.*.designation'    => ['nullable', 'string', 'max:255'],
            'lignes.*.quantite'       => ['nullable', 'numeric', 'gt:0'],
            'lignes.*.prix'           => ['nullable', 'numeric', 'min:0'],
        ]);

        $choisies = collect($validated['lignes'])->filter(fn ($l) => ! empty($l['commander']));

        if ($choisies->isEmpty()) {
            return back()->with('error', 'Cochez au moins une pièce à commander.');
        }

        $fournisseurCommande->load('lignes.product', 'lignes.demandePiece.latestSupplierOrderItem.supplierOrder');
        $lignesBc = $fournisseurCommande->lignes->keyBy('id');
        $erreurs = [];

        foreach ($choisies as $id => $saisie) {
            $ligne = $lignesBc->get((int) $id);
            $nom = $ligne?->designation ?: ($ligne?->reference ?: "#{$id}");

            if (! $ligne) {
                $erreurs[] = "Pièce #{$id} introuvable dans {$fournisseurCommande->numero}.";
            } elseif ($ligne->bon_transfert_id || $ligne->estPrete()) {
                $erreurs[] = "« {$nom} » est déjà disponible ou transférée : rien à commander.";
            } elseif ($this->ligneDejaCommandee($ligne)) {
                $erreurs[] = "« {$nom} » est déjà commandée (" . $ligne->demandePiece->latestSupplierOrderItem->supplierOrder->order_number . ').';
            } elseif (blank($saisie['reference'] ?? null)) {
                $erreurs[] = "« {$nom} » : la référence magasin est obligatoire.";
            } elseif (empty($saisie['quantite'])) {
                $erreurs[] = "« {$nom} » : indiquez la quantité.";
            }
        }

        if ($erreurs) {
            return back()->withInput()->with('error', implode(' ', $erreurs));
        }

        $vehicule = trim(implode(' ', array_filter([
            $fournisseurCommande->vehicule_marque,
            $fournisseurCommande->vehicule_modele,
            $fournisseurCommande->vehicule_immatriculation ? '(' . $fournisseurCommande->vehicule_immatriculation . ')' : null,
            $fournisseurCommande->vehicule_vin ? 'VIN ' . $fournisseurCommande->vehicule_vin : null,
        ])));
        $description = "Pour le BC garage {$fournisseurCommande->numero}"
            . ($fournisseurCommande->equipement ? " — consommation atelier : {$fournisseurCommande->equipement}" : '')
            . ($vehicule ? " — véhicule {$vehicule}" : '')
            . ($fournisseurCommande->client_nom ? " — client {$fournisseurCommande->client_nom}" : '');

        $supplier = \App\Models\Supplier::findOrFail($validated['supplier_id']);

        // V7 : une demande (source Atelier) ; le bon de commande du fournisseur
        // se crée tout seul et suit le circuit chef magasinier → admin.
        $lignes = [];

        foreach ($choisies as $id => $saisie) {
            $ligne = $lignesBc->get((int) $id);
            $reference = trim($saisie['reference']);

            // Pièce déjà au catalogue (même référence) : on la rattache ;
            // sinon la fiche sera créée à la réception.
            $product = $ligne->product
                ?? \App\Models\Product::query()->where('reference', $reference)->first();

            $lignes[] = [
                'product_id'                     => $product?->id,
                'reference'                      => $reference,
                'part_name'                      => trim($saisie['designation'] ?? '') ?: ($product?->designation ?? $ligne->designation),
                'quantity'                       => round((float) $saisie['quantite'], 2),
                'unit'                           => $product?->unit_label ?? 'Pièce',
                'supplier_id'                    => $supplier->id,
                'prix'                           => $saisie['prix'] ?? null,
                'description'                    => $description,
                'notes'                          => $description,
                'external_bon_commande_ligne_id' => $ligne->id,
            ];
        }

        try {
            $resultat = app(\App\Services\CircuitAchatService::class)->creerDemande([
                'source'                   => \App\Models\Demande::SOURCE_ATELIER,
                'depot_id'                 => $validated['depot_id'],
                'external_bon_commande_id' => $fournisseurCommande->id,
                'notes'                    => $description,
            ], $lignes);

            $supplierOrder = $resultat['bons']->first()
                ?? $resultat['demande']->bonsCommande()->latest('id')->first();

            if (! $supplierOrder) {
                throw new \RuntimeException('Le bon de commande fournisseur n\'a pas pu être créé.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->implode(' '));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Commande impossible : ' . $e->getMessage());
        }

        return redirect()
            ->route('supplier-orders.show', $supplierOrder)
            ->with('success', "Demande {$resultat['demande']->numero} : bon de commande {$supplierOrder->order_number} créé chez {$supplier->name} pour "
                . $choisies->count() . " pièce(s) du BC garage {$fournisseurCommande->numero}. Il attend la validation du chef magasinier, puis l'approbation de l'administrateur.");
    }

    /** La pièce a-t-elle déjà un bon de commande fournisseur actif (non annulé) ? */
    private function ligneDejaCommandee(ExternalBonCommandeLigne $ligne): bool
    {
        $commande = $ligne->demandePiece?->latestSupplierOrderItem?->supplierOrder;

        return $commande && ! in_array($commande->status, \App\Models\SupplierOrder::STATUTS_INACTIFS, true);
    }
}
