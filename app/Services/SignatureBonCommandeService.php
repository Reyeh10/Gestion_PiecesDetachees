<?php

namespace App\Services;

use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Signature électronique d'un bon de commande fournisseur (V7) : validation
 * du chef magasinier, puis approbation de l'admin.
 *
 * L'image PNG dessinée est gardée sur le disque local ; l'empreinte SHA-256 du
 * document (en-tête + lignes) au moment de la signature est enregistrée.
 */
class SignatureBonCommandeService
{
    private const PREFIXE = 'data:image/png;base64,';

    /** Fichier écrit par le dernier signer() : à effacer si la transaction échoue ensuite. */
    public ?string $dernierFichier = null;

    /** Règle de validation du champ « signature » (PNG en base64, non vide). */
    public static function regle(): array
    {
        return [
            'required',
            'string',
            'max:3000000',
            function (string $attribute, mixed $value, \Closure $fail) {
                if (! is_string($value) || ! str_starts_with($value, self::PREFIXE)) {
                    $fail('La signature électronique est invalide.');

                    return;
                }

                $binaire = base64_decode(substr($value, strlen(self::PREFIXE)), true);

                if ($binaire === false || strlen($binaire) < 100) {
                    $fail('La signature électronique est vide ou invalide.');

                    return;
                }

                if (! str_starts_with($binaire, "\x89PNG\r\n\x1a\n")) {
                    $fail('La signature doit être une image PNG valide.');
                }
            },
        ];
    }

    /**
     * Enregistre la signature (fichier + ligne). À appeler dans la transaction
     * qui change le statut du BC ; le fichier est effacé si elle échoue.
     */
    public function signer(SupplierOrder $bc, string $type, string $signature, Request $request): SupplierOrderSignature
    {
        $binaire = base64_decode(substr($signature, strlen(self::PREFIXE)), true);
        $chemin = 'supplier-order-signatures/' . $bc->id . '/' . $type . '-'
            . now()->format('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.png';

        if (! Storage::disk('local')->put($chemin, $binaire)) {
            throw new \RuntimeException('Impossible d\'enregistrer le fichier de signature.');
        }

        $this->dernierFichier = $chemin;

        try {
            return SupplierOrderSignature::create([
                'supplier_order_id' => $bc->id,
                'user_id'           => auth()->id(),
                'type'              => $type,
                'signature_path'    => $chemin,
                'signed_at'         => now(),
                'ip_address'        => $request->ip(),
                'user_agent'        => substr((string) $request->userAgent(), 0, 2000),
                'document_hash'     => $this->empreinte($bc),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($chemin);
            throw $e;
        }
    }

    /** Efface les signatures d'un type (refus, ligne retirée : il faut signer à nouveau). */
    public function effacer(SupplierOrder $bc, string $type): void
    {
        $bc->signatures()->where('type', $type)->get()->each(function (SupplierOrderSignature $s) {
            Storage::disk('local')->delete($s->signature_path);
            $s->delete();
        });
    }

    /** Empreinte du document signé : en-tête et lignes, montants formatés. */
    public function empreinte(SupplierOrder $bc): string
    {
        $f = fn ($v, $d = 2) => number_format((float) $v, $d, '.', '');

        $document = [
            'supplier_order' => [
                'id'                     => (int) $bc->id,
                'order_number'           => (string) $bc->order_number,
                'supplier_id'            => (int) $bc->supplier_id,
                'depot_id'               => $bc->depot_id !== null ? (int) $bc->depot_id : null,
                'created_by'             => $bc->created_by !== null ? (int) $bc->created_by : null,
                'order_date'             => $bc->order_date?->format('Y-m-d'),
                'expected_delivery_date' => $bc->expected_delivery_date?->format('Y-m-d'),
                'currency'               => (string) $bc->currency,
                'subtotal'               => $f($bc->subtotal),
                'discount_rate'          => $f($bc->discount_rate),
                'discount'               => $f($bc->discount),
                'shipping_cost'          => $f($bc->shipping_cost),
                'tax_rate'               => $f($bc->tax_rate),
                'tax_amount'             => $f($bc->tax_amount),
                'total'                  => $f($bc->total),
                'payment_terms'          => $bc->payment_terms,
                'delivery_terms'         => $bc->delivery_terms,
                'notes'                  => $bc->notes,
            ],
            'items' => $bc->items()->orderBy('id')->get()->map(fn (SupplierOrderItem $l) => [
                'id'                      => (int) $l->id,
                'vehicle_part_request_id' => $l->vehicle_part_request_id !== null ? (int) $l->vehicle_part_request_id : null,
                'product_id'              => $l->product_id !== null ? (int) $l->product_id : null,
                'reference'               => $l->reference,
                'description'             => $l->description,
                'unit'                    => $l->unit,
                'quantity_ordered'        => $f($l->quantity_ordered),
                'unit_price'              => $f($l->unit_price, 4),
                'line_total'              => $f($l->line_total),
                'notes'                   => $l->notes,
            ])->values()->all(),
        ];

        return hash('sha256', json_encode(
            $document,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
        ));
    }
}
