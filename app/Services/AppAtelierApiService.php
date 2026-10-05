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

    private function echecEnvoi(BonTransfert $bt, string $erreur): string
    {
        $bt->update(['envoi_atelier_erreur' => $erreur]);

        Log::warning("Envoi du bon de transfert {$bt->numero} à app-atelier en échec : {$erreur}");

        return $erreur;
    }
}
