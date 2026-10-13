<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Avoirs : annulation des factures (comme l'app Atelier). L'avoir est créé
 * par SaleController::cancel() ; ici on les liste, on les affiche / imprime
 * et on enregistre le remboursement du montant déjà encaissé.
 */
class AvoirController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $filtre = $request->get('filtre', '');

        $avoirs = Avoir::with(['sale', 'customer', 'creePar'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                        ->orWhere('motif', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($s) => $s->where('invoice_number', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filtre === 'a_rembourser', fn ($q) => $q->where('montant_a_rembourser', '>', 0)->whereNull('rembourse_le'))
            ->orderByDesc('annee')
            ->orderByDesc('sequence')
            ->paginate(25)
            ->withQueryString();

        return view('avoirs.index', compact('avoirs', 'search', 'filtre'));
    }

    public function show(Avoir $avoir)
    {
        return view('avoirs.show', ['avoir' => $this->charger($avoir)]);
    }

    public function pdf(Avoir $avoir)
    {
        return $this->genererPdf($avoir)->download($this->nomFichier($avoir));
    }

    /** PDF affiché dans le navigateur, pour l'impression directe. */
    public function imprimer(Avoir $avoir)
    {
        return $this->genererPdf($avoir)->stream($this->nomFichier($avoir));
    }

    /** Enregistre le remboursement au client du montant déjà encaissé. */
    public function rembourser(Request $request, Avoir $avoir)
    {
        if (! $avoir->resteARembourser()) {
            return back()->with('error', 'Aucun remboursement en attente sur cet avoir.');
        }

        $data = $request->validate([
            'mode_remboursement' => ['required', Rule::in(array_keys(Avoir::MODES_REMBOURSEMENT))],
            'rembourse_le'       => ['required', 'date', 'before_or_equal:today'],
        ], [
            'mode_remboursement.required' => 'Choisissez le mode de remboursement.',
            'rembourse_le.required'       => 'Indiquez la date du remboursement.',
            'rembourse_le.before_or_equal' => 'La date du remboursement ne peut pas être dans le futur.',
        ]);

        $avoir->update($data);

        return back()->with('success', "Remboursement de l'avoir {$avoir->numero} enregistré.");
    }

    private function charger(Avoir $avoir): Avoir
    {
        return $avoir->load(['sale.vehicle', 'customer', 'creePar', 'lignes.depot']);
    }

    private function genererPdf(Avoir $avoir): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('avoirs.pdf', ['avoir' => $this->charger($avoir)])
            ->setPaper('a4', 'portrait');
    }

    private function nomFichier(Avoir $avoir): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '-', $avoir->numero) . '.pdf';
    }
}
