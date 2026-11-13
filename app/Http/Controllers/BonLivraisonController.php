<?php

namespace App\Http\Controllers;

use App\Models\BonLivraison;
use App\Models\BonLivraisonLigne;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Bons de livraison (V6) : facultatifs, après la facture. On choisit les
 * pièces et les quantités livrées ; plusieurs BL par facture jusqu'à tout
 * livrer, jamais plus que facturé. Sans prix, sans effet sur le stock.
 */
class BonLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $bons = BonLivraison::with(['sale.customer', 'user'])
            ->withCount('lignes')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                        ->orWhere('recu_par', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($s) => $s->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")));
                });
            })
            ->orderByDesc('annee')
            ->orderByDesc('sequence')
            ->paginate(25)
            ->withQueryString();

        return view('bons-livraison.index', compact('bons', 'search'));
    }

    /** Formulaire : pièces de la facture avec facturé / déjà livré / reste. */
    public function create(Sale $sale)
    {
        if ($refus = $this->refus($sale)) {
            return redirect()->route('sales.show', $sale)->with('error', $refus);
        }

        $lignes = $this->etatLivraison($sale);

        if ($lignes->sum('reste') <= 0) {
            return redirect()->route('sales.show', $sale)->with('error', 'Toutes les pièces de cette facture sont déjà livrées.');
        }

        return view('bons-livraison.create', compact('sale', 'lignes'));
    }

    public function store(Request $request, Sale $sale)
    {
        if ($refus = $this->refus($sale)) {
            return redirect()->route('sales.show', $sale)->with('error', $refus);
        }

        $validated = $request->validate([
            'livre_le'       => ['required', 'date'],
            'recu_par'       => ['nullable', 'string', 'max:150'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'quantites'      => ['required', 'array'],
            'quantites.*'    => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $bl = DB::transaction(function () use ($sale, $validated) {
                // Verrou sur la facture : deux BL simultanés ne livrent pas deux fois.
                Sale::query()->lockForUpdate()->findOrFail($sale->id);

                $etat = $this->etatLivraison($sale)->keyBy('item_id');
                $aLivrer = [];

                foreach ($validated['quantites'] as $itemId => $quantite) {
                    $quantite = round((float) $quantite, 2);

                    if ($quantite <= 0) {
                        continue;
                    }

                    $ligne = $etat->get((int) $itemId);

                    if (! $ligne) {
                        throw new \RuntimeException("Pièce #{$itemId} introuvable sur la facture.");
                    }

                    if ($quantite > $ligne['reste'] + 0.0001) {
                        throw new \RuntimeException("« {$ligne['designation']} » : on ne peut pas livrer {$quantite}, il reste {$ligne['reste']} à livrer.");
                    }

                    $aLivrer[] = [$ligne, $quantite];
                }

                if (! $aLivrer) {
                    throw new \RuntimeException('Indiquez au moins une pièce à livrer.');
                }

                [$annee, $sequence, $numero] = BonLivraison::prochainNumero();

                $bl = BonLivraison::create([
                    'numero'   => $numero,
                    'annee'    => $annee,
                    'sequence' => $sequence,
                    'sale_id'  => $sale->id,
                    'user_id'  => auth()->id(),
                    'livre_le' => $validated['livre_le'],
                    'recu_par' => $validated['recu_par'] ?? null,
                    'notes'    => $validated['notes'] ?? null,
                ]);

                foreach ($aLivrer as [$ligne, $quantite]) {
                    BonLivraisonLigne::create([
                        'bon_livraison_id' => $bl->id,
                        'sale_item_id'     => $ligne['item_id'],
                        'product_id'       => $ligne['product_id'],
                        'reference'        => $ligne['reference'],
                        'designation'      => $ligne['designation'],
                        'unite'            => $ligne['unite'],
                        'quantite'         => $quantite,
                    ]);
                }

                return $bl;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('bons-livraison.show', $bl)
            ->with('success', "Bon de livraison {$bl->numero} créé pour la facture {$sale->invoice_number}.");
    }

    public function show(BonLivraison $bonLivraison)
    {
        return view('bons-livraison.show', ['bl' => $this->charger($bonLivraison)]);
    }

    public function pdf(BonLivraison $bonLivraison)
    {
        return $this->genererPdf($bonLivraison)->download($this->nomFichier($bonLivraison));
    }

    /** PDF affiché dans le navigateur, pour l'impression directe. */
    public function imprimer(BonLivraison $bonLivraison)
    {
        return $this->genererPdf($bonLivraison)->stream($this->nomFichier($bonLivraison));
    }

    /**
     * État de livraison de chaque pièce de la facture : facturé, déjà livré
     * (BL non annulés), reste à livrer. Utilisé aussi par la page de la vente.
     */
    public static function etatLivraison(Sale $sale)
    {
        $sale->loadMissing(['items.product']);

        $livre = BonLivraisonLigne::query()
            ->whereHas('bonLivraison', fn ($q) => $q->where('sale_id', $sale->id)->whereNull('annule_le'))
            ->selectRaw('sale_item_id, SUM(quantite) as total')
            ->groupBy('sale_item_id')
            ->pluck('total', 'sale_item_id');

        return $sale->items->map(function ($item) use ($livre) {
            $facture = round((float) $item->quantity, 2);
            $dejaLivre = round((float) ($livre[$item->id] ?? 0), 2);

            return [
                'item_id'     => $item->id,
                'product_id'  => $item->product_id,
                'reference'   => $item->product?->reference ?? $item->reference_libre,
                'designation' => $item->product?->designation ?? $item->designation_libre ?? 'Pièce',
                'unite'       => $item->product?->unit_label ?? 'Pièce',
                'facture'     => $facture,
                'livre'       => $dejaLivre,
                'reste'       => max(0, round($facture - $dejaLivre, 2)),
            ];
        })->values();
    }

    /** Pourquoi on ne peut pas faire de BL pour cette facture (null = possible). */
    private function refus(Sale $sale): ?string
    {
        if ($sale->status === 'cancelled') {
            return 'Facture annulée par un avoir : pas de bon de livraison.';
        }

        if (($sale->document_type ?? 'sale') !== 'sale') {
            return 'Un bon de livraison se fait seulement sur une facture.';
        }

        return null;
    }

    private function charger(BonLivraison $bl): BonLivraison
    {
        return $bl->load(['sale.customer', 'sale.vehicle', 'user', 'lignes']);
    }

    private function genererPdf(BonLivraison $bl)
    {
        return Pdf::loadView('bons-livraison.pdf', ['bl' => $this->charger($bl)])->setPaper('a4', 'portrait');
    }

    private function nomFichier(BonLivraison $bl): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '-', $bl->numero) . '.pdf';
    }
}
