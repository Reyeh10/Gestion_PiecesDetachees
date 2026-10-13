<?php

namespace App\Http\Controllers;

use App\Models\BonTransfert;
use App\Models\User;
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

    /** PDF affiché dans le navigateur (et non téléchargé), pour l'impression directe. */
    public function imprimer(BonTransfert $bonTransfert, BonTransfertService $service)
    {
        $bt = $this->charger($bonTransfert);

        return $service->pdf($bt)->stream($service->nomFichierPdf($bt));
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

    /**
     * Suppression manuelle d'un BT — administrateur uniquement (par exemple
     * devis supprimé côté garage). Case « pièces revenues au magasin » :
     * les pièces sont remises dans leur dépôt.
     */
    public function destroy(Request $request, BonTransfert $bonTransfert, BonTransfertService $service)
    {
        /**
 * Vérifier que l'utilisateur connecté est un administrateur.
 *
 * instanceof User permet à Intelephense de reconnaître
 * correctement les méthodes personnalisées du modèle.
 */
$user = auth()->user();

abort_unless(
    $user instanceof User && $user->isAdmin(),
    403,
    'Seul un administrateur peut supprimer un bon de transfert.'
);

        $numero = $bonTransfert->numero;
        $bcId = $bonTransfert->external_bon_commande_id;
        $remettre = $request->boolean('remettre_en_stock');

        $service->supprimer($bonTransfert, $remettre);

        $message = "Bon de transfert {$numero} supprimé."
            . ($remettre ? ' Les pièces ont été remises dans leurs dépôts.' : " Le stock n'a pas été modifié.");

        return $bcId
            ? redirect()->route('fournisseur-commandes.show', $bcId)->with('success', $message)
            : redirect()->route('bons-transfert.index')->with('success', $message);
    }

    private function charger(BonTransfert $bt): BonTransfert
    {
        return $bt->load([
            'bonCommande.lignes',
            'bonCommande.bonTransfert.lignes',
            'user',
            'lignes.product.depotStocks.rayon',
            'lignes.product.depotStocks.location',
            'lignes.depot',
        ]);
    }
}
