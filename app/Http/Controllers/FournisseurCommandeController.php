<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExternalBonCommande;
use App\Models\ExternalBonCommandeLigne;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Services\AppAtelierApiService;

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
            ->with(['lignes' => fn ($q) => $q->orderBy('position')])
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
     * Génère le prochain code client séquentiel au format ClXXX (ex: Cl003),
     * en suivant la même convention que la création manuelle de client.
     */
    private function prochainCodeClient(): string
    {
        $dernier = Customer::where('code', 'like', 'Cl%')
            ->get()
            ->map(fn (Customer $c) => (int) preg_replace('/\D/', '', $c->code))
            ->max();

        return 'Cl' . str_pad((string) (($dernier ?? 0) + 1), 3, '0', STR_PAD_LEFT);
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
     * Tant que la vente n'est pas créée, une ligne reste entièrement
     * modifiable : le vendeur peut re-sélectionner une autre pièce ou un
     * autre dépôt et re-valider. Dès que la vente existe, toute modification
     * est refusée (le stock a déjà été déduit).
     */
    public function updateLigne(Request $request, ExternalBonCommande $fournisseurCommande, ExternalBonCommandeLigne $ligne)
    {
        abort_unless($ligne->external_bon_commande_id === $fournisseurCommande->id, 404);

        if ($fournisseurCommande->vente_id) {
            return back()->with('error', 'La vente a déjà été créée à partir de ce bon : les lignes ne sont plus modifiables.');
        }

        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'depot_id'   => 'nullable|exists:depots,id',
            'note'       => 'nullable|string|max:255',
        ], [
            'note.max' => 'La note ne doit pas dépasser 255 caractères.',
        ]);

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

            return back()->with('success', 'Ligne marquée sans pièce correspondante et transmise au garage.');
        }

        $product = Product::findOrFail($productId);
        $depotId = $data['depot_id'] ?: null;

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
            return back()->with('error', "Cette pièce n'a pas de stock dans le dépôt choisi.");
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
            'note'                => $request->has('note') ? ($data['note'] ?? null) : $ligne->note,
        ]);

        if ($ligne->depot_id) {
            app(AppAtelierApiService::class)->envoyerDisponibilite($ligne);

            return back()->with('success', 'Disponibilité mise à jour et transmise au garage.');
        }

        return back()->with('success', 'Pièce identifiée. Choisissez le dépôt de prélèvement pour finaliser.');
    }

    /**
     * Convertit le bon de commande en vente réelle, une fois toutes les
     * pièces disponibles : crée/retrouve le client et le véhicule, puis
     * délègue à SaleController::store() pour la facture, la déduction de
     * stock et les mouvements de stock (même logique qu'une vente normale).
     */
    public function creerVente(ExternalBonCommande $fournisseurCommande, SaleController $saleController)
    {
        $fournisseurCommande->load('lignes.product');

        if ($fournisseurCommande->vente_id) {
            return redirect()->route('sales.show', $fournisseurCommande->vente_id);
        }

        $sansDepot = $fournisseurCommande->lignes
            ->filter(fn (ExternalBonCommandeLigne $ligne) => $ligne->product_id && ! $ligne->depot_id);

        if ($sansDepot->isNotEmpty()) {
            $refs = $sansDepot->map(fn ($l) => $l->reference ?: $l->designation)->implode(', ');

            return back()->with('error', "Impossible : choisissez d'abord le dépôt de prélèvement pour : {$refs}.");
        }

        if (! $fournisseurCommande->toutesPiecesDisponibles()) {
            return back()->with('error', "Impossible : toutes les pièces ne sont pas encore identifiées et disponibles pour {$fournisseurCommande->numero}.");
        }

        $telephone = trim((string) $fournisseurCommande->client_telephone);

        $customer = $telephone !== ''
            ? Customer::firstOrCreate(
                ['phone' => $telephone],
                ['code' => $this->prochainCodeClient(), 'name' => $fournisseurCommande->client_nom ?: 'Client app-atelier']
            )
            : Customer::create(['code' => $this->prochainCodeClient(), 'name' => $fournisseurCommande->client_nom ?: 'Client app-atelier']);

        $plaque = trim((string) $fournisseurCommande->vehicule_immatriculation);

        $vehicle = $plaque !== ''
            ? Vehicle::firstOrNew(['plate_number' => $plaque])
            : new Vehicle();

        $vehicle->customer_id = $customer->id;
        $vehicle->brand = $vehicle->brand ?: $fournisseurCommande->vehicule_marque;
        $vehicle->model = $vehicle->model ?: $fournisseurCommande->vehicule_modele;
        if ($plaque === '') {
            $vehicle->plate_number = 'INCONNU-' . $fournisseurCommande->numero;
        }
        $vehicle->save();

        $items = $fournisseurCommande->lignes->map(fn (ExternalBonCommandeLigne $ligne) => [
            'product_id' => $ligne->product_id,
            'depot_id'   => $ligne->depot_id,
            'quantity'   => (float) $ligne->quantite_demandee,
        ])->values()->all();

        $saleRequest = Request::create('', 'POST', [
            'customer_id'  => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'payment_type' => 'bon_commande',
            'items'        => $items,
        ]);
        $saleRequest->setUserResolver(fn () => auth()->user());
        $saleRequest->setLaravelSession(request()->session());

        $avantId = (int) Sale::max('id');

        $response = $saleController->store($saleRequest);

        $sale = Sale::where('id', '>', $avantId)
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->first();

        if (! $sale) {
            // La création a échoué (ex: stock insuffisant détecté à la dernière minute) :
            // on relaie le message d'erreur renvoyé par SaleController::store().
            return $response;
        }

        $fournisseurCommande->update(['vente_id' => $sale->id]);

        return redirect()->route('sales.show', $sale)
            ->with('success', "Vente créée à partir de {$fournisseurCommande->numero}.");
    }
}
