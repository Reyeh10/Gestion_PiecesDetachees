<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierOrderSignature extends Model
{
    /*
    |--------------------------------------------------------------------------
    | TYPES DE SIGNATURE
    |--------------------------------------------------------------------------
    */

    public const TYPE_PREPARED = 'prepared';

    public const TYPE_APPROVED = 'approved';


    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTS AUTORISÉS
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'supplier_order_id',

        'user_id',

        'type',

        'signature_path',

        'signed_at',

        'ip_address',

        'user_agent',

        'document_hash',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'signed_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | BON DE COMMANDE
    |--------------------------------------------------------------------------
    */

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(
            SupplierOrder::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UTILISATEUR SIGNATAIRE
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIGNATURE DU PRÉPARATEUR ?
    |--------------------------------------------------------------------------
    */

    public function isPreparedSignature(): bool
    {
        return $this->type
            ===
            self::TYPE_PREPARED;
    }


    /*
    |--------------------------------------------------------------------------
    | SIGNATURE DE L'APPROBATEUR ?
    |--------------------------------------------------------------------------
    */

    public function isApprovedSignature(): bool
    {
        return $this->type
            ===
            self::TYPE_APPROVED;
    }
}
