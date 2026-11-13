<?php

namespace App\Http\Controllers;

use App\Models\BonTransfert;
use App\Models\ExternalBonCommande;
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

        // V6 : filtre par type — commandes garage, bus, ou un motif de BT direct
        $type = (string) $request->get('type', '');
        $types = ['garage' => 'Commandes garage', 'bus' => 'Bus leasing / transport'] + ExternalBonCommande::MOTIFS;
        $type = array_key_exists($type, $types) ? $type : '';

        /*
         * Recherche : chaque mot doit se retrouver quelque part (n° BT, n° BC,
         * client, téléphone, marque, modèle, immatriculation, créé par, ou une
         * pièce du BT). « Toyota Hilux » ou un numéro de téléphone marchent.
         */
        $mots = array_filter(preg_split('/\s+/', $search));

        $bons = BonTransfert::with(['bonCommande', 'user'])
            ->withCount('lignes')
            ->when($mots, function ($query) use ($mots) {
                foreach ($mots as $mot) {
                    $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $mot) . '%';
                    $query->where(function ($q) use ($like) {
                        $q->where('numero', 'like', $like)
                            ->orWhereHas('bonCommande', fn ($bc) => $bc->where('numero', 'like', $like)
                                ->orWhere('client_nom', 'like', $like)
                                ->orWhere('client_telephone', 'like', $like)
                                ->orWhere('vehicule_marque', 'like', $like)
                                ->orWhere('vehicule_modele', 'like', $like)
                                ->orWhere('vehicule_immatriculation', 'like', $like)
                                ->orWhere('beneficiaire', 'like', $like)
                                ->orWhere('equipement', 'like', $like)
                                ->orWhere('demande_par', 'like', $like))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like))
                            ->orWhereHas('lignes', fn ($l) => $l->where('designation_garage', 'like', $like)
                                ->orWhereHas('product', fn ($pr) => $pr->where('reference', 'like', $like)->orWhere('designation', 'like', $like)));
                    });
                }
            })
            ->when($type !== '', fn ($query) => $query->whereHas('bonCommande', fn ($bc) => match ($type) {
                'garage'                          => $bc->garage(),
                'bus'                             => $bc->sortiesBus(),
                ExternalBonCommande::MOTIF_CONSO  => $bc->consommations(),
                default                           => $bc->where('source_system', ExternalBonCommande::SOURCE_INTERNE)->where('motif', $type),
            }))
            ->orderByDesc('annee')
            ->orderByDesc('sequence')
            ->paginate($this->bonsParPage($request))
            ->withQueryString();

        return view('bons-transfert.index', [
            'bons'   => $bons,
            'search' => $search,
            'type'   => $type,
            'types'  => $types,
        ]);
    }

    /* Nombre de BT par page (10, 25, 50 ou 100), mémorisé pour la session. */
    public const PAR_PAGE = [10, 25, 50, 100];

    private function bonsParPage(Request $request): int
    {
        $demande = (int) $request->query('par_page');

        if (in_array($demande, self::PAR_PAGE, true)) {
            session(['bons_transfert_par_page' => $demande]);

            return $demande;
        }

        $memorise = (int) session('bons_transfert_par_page', 25);

        return in_array($memorise, self::PAR_PAGE, true) ? $memorise : 25;
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

        // V6 : un BT bus s'annule aussi dans l'Atelier
        if ($bonTransfert->bonCommande?->estBus()) {
            return app(BusController::class)->destroy($request, $bonTransfert);
        }

        // V6 : un BT direct « + BT » (la consommation atelier s'annule aussi dans l'Atelier)
        if ($bonTransfert->bonCommande?->estBtDirect()) {
            return app(BtDirectController::class)->destroy($request, $bonTransfert);
        }

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
