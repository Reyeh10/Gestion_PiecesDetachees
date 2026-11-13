<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Depot;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\Vehicle;
use App\Models\VehiclePartRequest;
use App\Services\CircuitAchatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * V7 — Demandes de pièces : on enregistre plusieurs pièces ensemble ; dès
 * qu'une pièce a son fournisseur, le bon de commande de ce fournisseur se
 * crée tout seul (App\Services\CircuitAchatService).
 */
class DemandeController extends Controller
{
    public function __construct(private CircuitAchatService $circuit)
    {
    }

    public function index(Request $request): View
    {
        // Onglets : toutes, une étape (Demande::ETAPES) ou « pièces à compléter »
        $onglet = (string) $request->query('onglet', 'toutes');

        if ($onglet !== 'a-completer' && ! array_key_exists($onglet, Demande::ETAPES)) {
            $onglet = 'toutes';
        }

        $comptes = collect(Demande::ETAPES)->map(fn ($e, $cle) => Demande::query()->etape($cle)->count())->all();

        // Pièces qui attendent encore leur fournisseur (« À compléter »)
        $aCompleterQuery = VehiclePartRequest::query()
            ->whereIn('status', [VehiclePartRequest::STATUS_SEARCHING, VehiclePartRequest::STATUS_NOT_FOUND])
            ->where(fn ($q) => $q->whereNull('supplier_id')->orWhere(fn ($r) => $r->whereNull('product_id')->whereNull('reference')));
        $nombreACompleter = (clone $aCompleterQuery)->count();

        if ($onglet === 'a-completer') {
            $pieces = $aCompleterQuery
                ->with(['demande', 'vehicle', 'depot', 'product', 'supplier'])
                ->when($request->filled('search'), fn ($q) => $this->filtrePiece($q, trim($request->query('search'))))
                ->orderBy('requested_at')
                ->paginate(25)
                ->withQueryString();

            return view('demandes.index', [
                'onglet'           => $onglet,
                'pieces'           => $pieces,
                'demandes'         => null,
                'comptes'          => $comptes,
                'nombreACompleter' => $nombreACompleter,
                'suppliers'        => Supplier::query()->orderBy('name')->get(['id', 'name']),
            ]);
        }

        $demandes = Demande::query()
            ->with(['vehicle', 'depot', 'creator', 'pieces:id,demande_id,status', 'bonsCommande:id,demande_id,order_number,status,supplier_id', 'bonsCommande.supplier:id,name'])
            ->withCount('pieces')
            ->when($request->filled('search'), function ($q) use ($request) {
                $mot = trim($request->query('search'));
                $q->where(fn ($w) => $w
                    ->where('numero', 'like', "%{$mot}%")
                    ->orWhereHas('pieces', fn ($p) => $this->filtrePiece($p, $mot))
                    ->orWhereHas('bonsCommande', fn ($b) => $b->where('order_number', 'like', "%{$mot}%"))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate_number', 'like', "%{$mot}%")));
            })
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->query('source')))
            ->when($onglet !== 'toutes', fn ($q) => $q->etape($onglet))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('demandes.index', [
            'onglet'           => $onglet,
            'pieces'           => null,
            'demandes'         => $demandes,
            'comptes'          => $comptes,
            'nombreACompleter' => $nombreACompleter,
            'suppliers'        => collect(),
        ]);
    }

    private function filtrePiece($q, string $mot)
    {
        return $q->where(fn ($w) => $w
            ->where('reference', 'like', "%{$mot}%")
            ->orWhere('part_name', 'like', "%{$mot}%"));
    }

    public function create(Request $request): View
    {
        return view('demandes.create', $this->donneesFormulaire() + [
            'vehicleId' => $request->integer('vehicle_id') ?: null,
            'depotId'   => $request->integer('depot_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'depot_id'   => ['required', 'integer', 'exists:depots,id'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ] + $this->reglesLignes(), $this->messages());

        try {
            $resultat = $this->circuit->creerDemande([
                'source'     => Demande::SOURCE_SAISIE,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'depot_id'   => $data['depot_id'],
                'notes'      => $data['notes'] ?? null,
            ], $this->lignes($data['lignes']));
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()
            ->route('demandes.show', $resultat['demande'])
            ->with('success', 'Demande ' . $resultat['demande']->numero . ' enregistrée. ' . $this->messageBons($resultat['bons']));
    }

    public function show(Demande $demande): View
    {
        $demande->load([
            'vehicle.customer', 'depot', 'creator', 'bonCommandeGarage',
            'pieces' => fn ($q) => $q->orderBy('id'),
            'pieces.supplier', 'pieces.product', 'pieces.ligneBcActive.supplierOrder',
            'bonsCommande' => fn ($q) => $q->orderBy('id'),
            'bonsCommande.supplier', 'bonsCommande.items', 'bonsCommande.validator', 'bonsCommande.approver',
        ]);

        return view('demandes.show', $this->donneesFormulaire() + [
            'demande' => $demande,
        ]);
    }

    /** « Ajouter une pièce » : nouvelles lignes ; après l'envoi, elles vont sur un BC complémentaire. */
    public function ajouterPieces(Request $request, Demande $demande): RedirectResponse
    {
        $data = $request->validate($this->reglesLignes(), $this->messages());

        try {
            $bons = $this->circuit->ajouterPieces($demande, $this->lignes($data['lignes']));
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', count($data['lignes']) . ' pièce(s) ajoutée(s). ' . $this->messageBons($bons));
    }

    /** Fournisseur (et prix indicatif) d'une pièce : son BC se crée ou se complète. */
    public function fournisseur(Request $request, VehiclePartRequest $piece): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'prix'        => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $bons = $this->circuit->definirFournisseur(
                $piece,
                isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                isset($data['prix']) ? (float) $data['prix'] : null
            );
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $bc = $this->circuit->ligneActive($piece->fresh())?->supplierOrder;

        return back()->with('success', '« ' . $piece->part_name . ' » : '
            . ($bc ? 'sur le bon de commande ' . $bc->order_number . ($bons->contains('id', $bc->id) ? ' (créé, à valider par le chef magasinier).' : '.') : 'fournisseur retiré, la pièce est de nouveau « En recherche ».'));
    }

    /** Dépôt de réception manquant (import pour un véhicule) : les BC peuvent alors se créer. */
    public function depot(Request $request, Demande $demande): RedirectResponse
    {
        $data = $request->validate(['depot_id' => ['required', 'integer', 'exists:depots,id']]);

        $demande->update(['depot_id' => $data['depot_id']]);
        $bons = \Illuminate\Support\Facades\DB::transaction(fn () => $this->circuit->genererBonsCommande($demande));

        return back()->with('success', 'Dépôt de réception enregistré. ' . $this->messageBons($bons));
    }

    /* ------------------------------------------------------------------ */

    private function reglesLignes(): array
    {
        return [
            'lignes'               => ['required', 'array', 'min:1'],
            'lignes.*.product_id'  => ['nullable', 'integer', 'exists:products,id'],
            'lignes.*.reference'   => ['nullable', 'string', 'max:255'],
            'lignes.*.part_name'   => ['nullable', 'string', 'max:255', 'required_without:lignes.*.product_id'],
            'lignes.*.quantity'    => ['required', 'numeric', 'gt:0'],
            'lignes.*.unit'        => ['nullable', 'string', 'max:50'],
            'lignes.*.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'lignes.*.prix'        => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function messages(): array
    {
        return [
            'depot_id.required'                => 'Choisissez le dépôt de réception.',
            'lignes.required'                  => 'Ajoutez au moins une pièce.',
            'lignes.*.part_name.required_without' => 'Chaque ligne doit avoir une pièce du catalogue ou une désignation.',
            'lignes.*.quantity.required'       => 'Indiquez la quantité de chaque pièce.',
            'lignes.*.quantity.gt'             => 'La quantité doit être supérieure à 0.',
            'lignes.*.prix.min'                => 'Le prix ne peut pas être négatif.',
        ];
    }

    /** Lignes saisies → lignes du service (pièce du catalogue ou référence libre). */
    private function lignes(array $lignes): array
    {
        return collect($lignes)->map(fn ($l) => [
            'product_id'  => $l['product_id'] ?? null,
            'reference'   => empty($l['product_id']) ? ($l['reference'] ?? null) : null,
            'part_name'   => empty($l['product_id']) ? ($l['part_name'] ?? null) : null,
            'quantity'    => $l['quantity'],
            'unit'        => $l['unit'] ?? null,
            'supplier_id' => $l['supplier_id'] ?? null,
            'prix'        => $l['prix'] ?? null,
        ])->values()->all();
    }

    private function messageBons(Collection $bons): string
    {
        if ($bons->isEmpty()) {
            return 'Aucun nouveau bon de commande : indiquez le fournisseur des pièces pour qu\'il se crée.';
        }

        return $bons->count() === 1
            ? 'Bon de commande ' . $bons->first()->order_number . ' créé automatiquement : il attend la validation du chef magasinier.'
            : $bons->count() . ' bons de commande créés automatiquement (' . $bons->pluck('order_number')->implode(', ') . ') : ils attendent la validation du chef magasinier.';
    }

    /** Catalogue (avec stock par dépôt pour l'alerte « déjà en stock »), véhicules, dépôts, fournisseurs. */
    private function donneesFormulaire(): array
    {
        $products = Product::query()
            ->with(['depotStocks' => fn ($q) => $q->where('quantity', '>', 0), 'depotStocks.depot:id,name'])
            ->orderBy('designation')
            ->get(['id', 'reference', 'designation', 'unit_label', 'purchase_price']);

        return [
            'pieces' => $products->map(fn ($p) => [
                'id'     => $p->id,
                'text'   => $p->reference . ' — ' . $p->designation,
                'ref'    => $p->reference,
                'des'    => $p->designation,
                'unite'  => $p->unit_label,
                'prix'   => round((float) $p->purchase_price, 2),
                'stocks' => $p->depotStocks->map(fn ($s) => ['depot' => $s->depot->name ?? ('#' . $s->depot_id), 'q' => (float) $s->quantity])->values(),
            ])->values(),
            'vehicles'  => Vehicle::query()->with('customer:id,name')->orderBy('plate_number')->get(['id', 'plate_number', 'brand', 'model', 'customer_id']),
            'depots'    => Depot::query()->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
