<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\ExternalBonCommandeLigne;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Renvoie vers app-atelier la disponibilité, le prix de vente et une
 * éventuelle note d'une ligne dès qu'elle est identifiée manuellement par
 * le vendeur (cas des lignes sans référence). Appel synchrone, non
 * bloquant : si app-atelier est injoignable, on journalise l'échec mais
 * la mise à jour reste bien enregistrée ici.
 */
class AppAtelierApiService
{
    public function envoyerDisponibilite(ExternalBonCommandeLigne $ligne): void
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');

        if (! $url || ! $token) {
            Log::warning("Envoi app-atelier ignoré pour la ligne #{$ligne->id} : APP_ATELIER_URL/TOKEN non configurés.");
            return;
        }

        $numero = $ligne->externalBonCommande->numero;

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->withoutRedirecting()
                ->timeout(5)
                ->patch(rtrim($url, '/')."/bons-commande/{$numero}/lignes/{$ligne->position}", [
                    'disponible'          => (bool) $ligne->disponible,
                    'quantite_disponible' => $ligne->quantite_disponible,
                    'prix_unitaire'       => $ligne->prix_unitaire,
                    'note'                => $ligne->note,
                ]);

            if (! $response->successful()) {
                Log::warning("Réponse app-atelier en échec pour {$numero} ligne {$ligne->position} : HTTP {$response->status()} — {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning("Impossible de joindre app-atelier pour {$numero} ligne {$ligne->position} : {$e->getMessage()}");
        }
    }

    /**
     * Envoie le bon de transfert (numéro, date, dépôt(s), lignes + PDF) à
     * l'Atelier, qui le rattache au bon de commande. Un nouvel envoi remplace
     * le précédent côté Atelier : on peut donc renvoyer sans risque.
     *
     * Le résultat est mémorisé sur le BT (envoye_atelier_at ou
     * envoi_atelier_erreur). Renvoie null si l'envoi a réussi, sinon le
     * message d'erreur.
     */
    public function envoyerBonTransfert(BonTransfert $bt): ?string
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');

        $bt->loadMissing(['bonCommande', 'lignes.product', 'lignes.depot']);

        if ($bt->bonCommande?->estBus()) {
            return $this->envoyerBonTransfertBus($bt);
        }

        if ($bt->bonCommande?->estConsommationDirecte()) {
            return $this->envoyerBonTransfertConsommation($bt);
        }

        // BT direct « usage interne » (intersociété, équipe commerciale…) : pas pour le garage
        if ($bt->bonCommande?->estInterne()) {
            return "BT interne ({$bt->bonCommande->libelleMotif()}) : il ne part pas à l'Atelier.";
        }

        $numeroBc = $bt->bonCommande->numero;

        if (! $url || ! $token) {
            return $this->echecEnvoi($bt, 'APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.');
        }

        $champs = [
            'numero' => $bt->numero,
            'date'   => $bt->created_at->toDateString(),
            'depot'  => mb_substr($bt->lignes->map(fn ($l) => $l->depot?->name)->filter()->unique()->implode(', '), 0, 100),
        ];

        foreach ($bt->lignes->values() as $i => $ligne) {
            $champs["lignes[{$i}][index]"] = (string) $ligne->position;
            $champs["lignes[{$i}][reference]"] = (string) ($ligne->product?->reference ?? '');
            $champs["lignes[{$i}][designation]"] = (string) ($ligne->designation_garage ?: $ligne->product?->designation ?? '');
            $champs["lignes[{$i}][quantite]"] = (string) (float) $ligne->quantite;
        }

        try {
            $bts = app(BonTransfertService::class);

            $response = Http::withToken($token)
                ->acceptJson()
                ->withoutRedirecting()
                ->timeout(30)
                ->attach('fichier', $bts->pdf($bt)->output(), $bts->nomFichierPdf($bt), ['Content-Type' => 'application/pdf'])
                ->post(rtrim($url, '/').'/bons-commande/'.rawurlencode($numeroBc).'/bon-transfert', $champs);
        } catch (\Throwable $e) {
            return $this->echecEnvoi($bt, "Impossible de joindre l'app Atelier : {$e->getMessage()}");
        }

        if ($response->successful()) {
            $bt->update(['envoye_atelier_at' => now(), 'envoi_atelier_erreur' => null]);

            return null;
        }

        $detail = match ($response->status()) {
            401     => "jeton refusé par l'Atelier (APP_ATELIER_TOKEN).",
            404     => "bon de commande {$numeroBc} introuvable côté Atelier.",
            default => $response->json('message') ?: mb_substr($response->body(), 0, 300),
        };

        return $this->echecEnvoi($bt, "HTTP {$response->status()} — {$detail}");
    }

    /*
    |--------------------------------------------------------------------------
    | V6 — Bus leasing / bus du transport public
    |--------------------------------------------------------------------------
    | Contrat avec l'Atelier (même jeton que les autres appels) :
    | - GET    {url}/bus                       → {"bus": [{id, groupe, immatriculation, marque, modele, vin}]}
    | - POST   {url}/bus/bons-transfert        → crée / met à jour le BC bus « reçu » (réponse : bon_commande)
    | - DELETE {url}/bus/bons-transfert/{n°}   → annule ; 409 si déjà compté dans un récap
    */

    /**
     * Liste des bus (bus leasing + bus du transport public) tenue dans l'Atelier.
     *
     * @return array<int, array{id:?int, groupe:string, immatriculation:string, marque:?string, modele:?string, vin:?string}>
     *
     * @throws \RuntimeException si l'Atelier est injoignable ou répond en erreur
     */
    public function listeBus(): array
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');

        if (! $url || ! $token) {
            throw new \RuntimeException('APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.');
        }

        try {
            $response = Http::withToken($token)->acceptJson()->withoutRedirecting()->timeout(10)
                ->get(rtrim($url, '/').'/bus');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Impossible de joindre l'app Atelier : {$e->getMessage()}");
        }

        if (! $response->successful() || ! is_array($response->json('bus'))) {
            throw new \RuntimeException("Liste des bus refusée par l'Atelier (HTTP {$response->status()}).");
        }

        return $response->json('bus');
    }

    /**
     * Envoie un BT bus : l'Atelier enregistre (ou met à jour, même n° de BT)
     * un BC bus déjà « reçu », avec les pièces et leur prix de vente HT, pour
     * son récap mensuel.
     */
    private function envoyerBonTransfertBus(BonTransfert $bt): ?string
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');
        $bc = $bt->bonCommande;

        if (! $url || ! $token) {
            return $this->echecEnvoi($bt, 'APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.');
        }

        $champs = [
            'numero'          => $bt->numero,
            'date'            => $bt->created_at->toDateString(),
            'groupe'          => (string) $bc->groupe_bus,
            'immatriculation' => (string) $bc->vehicule_immatriculation,
            'depot'           => mb_substr($bt->lignes->map(fn ($l) => $l->depot?->name)->filter()->unique()->implode(', '), 0, 100),
        ];

        foreach ($bt->lignes->values() as $i => $ligne) {
            $champs["lignes[{$i}][reference]"] = (string) ($ligne->product?->reference ?? '');
            $champs["lignes[{$i}][designation]"] = (string) ($ligne->product?->designation ?: $ligne->designation_garage ?: '');
            $champs["lignes[{$i}][quantite]"] = (string) (float) $ligne->quantite;
            $champs["lignes[{$i}][prix_unitaire]"] = (string) (float) $ligne->prix_unitaire;
        }

        try {
            $bts = app(BonTransfertService::class);

            $response = Http::withToken($token)
                ->acceptJson()
                ->withoutRedirecting()
                ->timeout(30)
                ->attach('fichier', $bts->pdf($bt)->output(), $bts->nomFichierPdf($bt), ['Content-Type' => 'application/pdf'])
                ->post(rtrim($url, '/').'/bus/bons-transfert', $champs);
        } catch (\Throwable $e) {
            return $this->echecEnvoi($bt, "Impossible de joindre l'app Atelier : {$e->getMessage()}");
        }

        if ($response->successful()) {
            $bt->update([
                'envoye_atelier_at'    => now(),
                'envoi_atelier_erreur' => null,
                'atelier_bc_numero'    => $response->json('bon_commande') ?: $bt->atelier_bc_numero,
            ]);

            return null;
        }

        $detail = match ($response->status()) {
            401     => "jeton refusé par l'Atelier (APP_ATELIER_TOKEN).",
            404     => "bus {$bc->vehicule_immatriculation} inconnu de l'Atelier (ou réception des BT bus pas encore en place).",
            409     => $response->json('message') ?: 'BT déjà compté dans un récap : modification refusée par l\'Atelier.',
            default => $response->json('message') ?: mb_substr($response->body(), 0, 300),
        };

        return $this->echecEnvoi($bt, "HTTP {$response->status()} — {$detail}");
    }

    /**
     * Demande à l'Atelier d'annuler un BT bus (avant sa suppression ici).
     * Renvoie null si c'est fait (ou si l'Atelier ne l'avait jamais reçu),
     * sinon le motif du refus — par exemple BT déjà compté dans un récap.
     */
    public function annulerBonTransfertBus(BonTransfert $bt): ?string
    {
        return $this->annulerBonTransfertInterne($bt, 'bus/bons-transfert');
    }

    /*
    |--------------------------------------------------------------------------
    | V6 — Consommation atelier (équipements du garage)
    |--------------------------------------------------------------------------
    | - GET    {url}/equipements                         → {"equipements": [{id, nom}]}
    | - POST   {url}/consommations/bons-transfert        → crée / met à jour le BC consommation « reçu »
    | - DELETE {url}/consommations/bons-transfert/{n°}   → annule le BC consommation
    */

    /**
     * Équipements actifs du garage (ponts élévateurs, compresseur…).
     *
     * @return array<int, array{id:int, nom:string}>
     *
     * @throws \RuntimeException si l'Atelier est injoignable ou répond en erreur
     */
    public function listeEquipements(): array
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');

        if (! $url || ! $token) {
            throw new \RuntimeException('APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.');
        }

        try {
            $response = Http::withToken($token)->acceptJson()->withoutRedirecting()->timeout(10)
                ->get(rtrim($url, '/').'/equipements');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Impossible de joindre l'app Atelier : {$e->getMessage()}");
        }

        if (! $response->successful() || ! is_array($response->json('equipements'))) {
            throw new \RuntimeException("Liste des équipements refusée par l'Atelier (HTTP {$response->status()}).");
        }

        return $response->json('equipements');
    }

    /**
     * Envoie un BT direct de consommation : l'Atelier enregistre (ou met à
     * jour, même n° de BT) un BC consommation déjà « reçu », avec les prix.
     */
    private function envoyerBonTransfertConsommation(BonTransfert $bt): ?string
    {
        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');
        $bc = $bt->bonCommande;
        $bc->loadMissing('equipementAtelier');

        if (! $url || ! $token) {
            return $this->echecEnvoi($bt, 'APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.');
        }

        $champs = [
            'numero'        => $bt->numero,
            'date'          => $bt->created_at->toDateString(),
            'equipement_id' => (string) ($bc->equipementAtelier?->atelier_id ?? ''),
            'equipement'    => (string) $bc->equipement,
            'demande_par'   => (string) ($bc->demande_par ?? ''),
            'depot'         => mb_substr($bt->lignes->map(fn ($l) => $l->depot?->name)->filter()->unique()->implode(', '), 0, 100),
        ];

        foreach ($bt->lignes->values() as $i => $ligne) {
            $champs["lignes[{$i}][reference]"] = (string) ($ligne->product?->reference ?? '');
            $champs["lignes[{$i}][designation]"] = (string) ($ligne->product?->designation ?: $ligne->designation_garage ?: '');
            $champs["lignes[{$i}][quantite]"] = (string) (float) $ligne->quantite;
            $champs["lignes[{$i}][prix_unitaire]"] = (string) (float) $ligne->prix_unitaire;
        }

        try {
            $bts = app(BonTransfertService::class);

            $response = Http::withToken($token)
                ->acceptJson()
                ->withoutRedirecting()
                ->timeout(30)
                ->attach('fichier', $bts->pdf($bt)->output(), $bts->nomFichierPdf($bt), ['Content-Type' => 'application/pdf'])
                ->post(rtrim($url, '/').'/consommations/bons-transfert', $champs);
        } catch (\Throwable $e) {
            return $this->echecEnvoi($bt, "Impossible de joindre l'app Atelier : {$e->getMessage()}");
        }

        if ($response->successful()) {
            $bt->update([
                'envoye_atelier_at'    => now(),
                'envoi_atelier_erreur' => null,
                'atelier_bc_numero'    => $response->json('bon_commande') ?: $bt->atelier_bc_numero,
            ]);

            return null;
        }

        $detail = match ($response->status()) {
            401     => "jeton refusé par l'Atelier (APP_ATELIER_TOKEN).",
            404     => $response->json('message') ?: "équipement « {$bc->equipement} » inconnu de l'Atelier.",
            default => $response->json('message') ?: mb_substr($response->body(), 0, 300),
        };

        return $this->echecEnvoi($bt, "HTTP {$response->status()} — {$detail}");
    }

    public function annulerBonTransfertConsommation(BonTransfert $bt): ?string
    {
        return $this->annulerBonTransfertInterne($bt, 'consommations/bons-transfert');
    }

    /**
     * Demande à l'Atelier d'annuler un BT interne (bus, consommation) avant
     * sa suppression ici. Renvoie null si c'est fait (ou si l'Atelier ne
     * l'avait jamais reçu), sinon le motif du refus — par exemple BT déjà
     * compté dans un récap.
     */
    private function annulerBonTransfertInterne(BonTransfert $bt, string $chemin): ?string
    {
        if (! $bt->envoye_atelier_at) {
            return null; // jamais arrivé à l'Atelier : rien à annuler
        }

        $url = config('services.app_atelier.url');
        $token = config('services.app_atelier.token');

        if (! $url || ! $token) {
            return 'APP_ATELIER_URL / APP_ATELIER_TOKEN non configurés.';
        }

        try {
            $response = Http::withToken($token)->acceptJson()->withoutRedirecting()->timeout(15)
                ->delete(rtrim($url, '/').'/'.$chemin.'/'.rawurlencode($bt->numero));
        } catch (\Throwable $e) {
            return "Impossible de joindre l'app Atelier : {$e->getMessage()}";
        }

        if ($response->successful() || $response->status() === 404) {
            return null;
        }

        return match ($response->status()) {
            401     => "jeton refusé par l'Atelier (APP_ATELIER_TOKEN).",
            409     => $response->json('message') ?: "{$bt->numero} est déjà compté dans un récap de l'Atelier : suppression refusée.",
            default => "HTTP {$response->status()} — ".($response->json('message') ?: mb_substr($response->body(), 0, 300)),
        };
    }

    private function echecEnvoi(BonTransfert $bt, string $erreur): string
    {
        $bt->update(['envoi_atelier_erreur' => $erreur]);

        Log::warning("Envoi du bon de transfert {$bt->numero} à app-atelier en échec : {$erreur}");

        return $erreur;
    }
}
