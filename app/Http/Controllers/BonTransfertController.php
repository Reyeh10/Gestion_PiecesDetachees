<?php

namespace App\Http\Controllers;

use App\Models\BonTransfert;
use App\Services\AppAtelierApiService;
use App\Services\BonTransfertService;
use Illuminate\Http\Request;

/**
 * Bons de transfert (BT) : pièces transférées au garage à partir des bons
 * de commande (BC) reçus de l'app Atelier. Remplacent la vente/facture.
 */
class BonTransfertController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));

        $bons = BonTransfert::with(['bonCommande', 'user'])
            ->withCount('lignes')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                        ->orWhereHas('bonCommande', function ($bc) use ($search) {
                            $bc->where('numero', 'like', "%{$search}%")
                                ->orWhere('vehicule_immatriculation', 'like', "%{$search}%")
                                ->orWhere('client_nom', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('annee')
            ->orderByDesc('sequence')
            ->paginate(25)
            ->withQueryString();

        return view('bons-transfert.index', [
            'bons'   => $bons,
            'search' => $search,
        ]);
    }

    public function show(BonTransfert $bonTransfert)
    {
        return view('bons-transfert.show', [
            'bt' => $this->charger($bonTransfert),
        ]);
    }

    public function pdf(BonTransfert $bonTransfert, BonTransfertService $service)
    {
        $bt = $this->charger($bonTransfert);

        return $service->pdf($bt)->download($service->nomFichierPdf($bt));
    }

    /** (Re)envoie le bon de transfert à l'app Atelier (garage). */
    public function envoyer(BonTransfert $bonTransfert, AppAtelierApiService $atelier)
    {
        $erreur = $atelier->envoyerBonTransfert($bonTransfert);

        return back()->with(
            $erreur ? 'error' : 'success',
            $erreur
                ? "Envoi de {$bonTransfert->numero} au garage échoué : {$erreur}"
                : "Bon de transfert {$bonTransfert->numero} envoyé au garage."
        );
    }

    private function charger(BonTransfert $bt): BonTransfert
    {
        return $bt->load([
            'bonCommande.lignes',
            'bonCommande.bonTransfert.lignes',
            'user',
            'lignes.product',
            'lignes.depot',
        ]);
    }
}
