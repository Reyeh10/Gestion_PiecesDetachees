<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoldProductsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    protected ?string $search;

    protected ?string $dateFrom;

    protected ?string $dateTo;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTEUR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->search = $search
            ? trim($search)
            : null;

        $this->dateFrom = $dateFrom ?: null;

        $this->dateTo = $dateTo ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | COLLECTION
    |--------------------------------------------------------------------------
    */

    public function collection(): Collection
    {
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;

        $query = Product::with([
            'brand',
            'model',
            'family',
            'subfamily',
            'rayon',
            'location',
        ])
        ->withSum([
            'saleItems as sold_quantity' => function ($query) use (
                $dateFrom,
                $dateTo
            ) {
                $query->whereHas(
                    'sale',
                    function ($q) use ($dateFrom, $dateTo) {

                        /*
                        |--------------------------------------------------------------------------
                        | EXCLURE LES VENTES ANNULÉES
                        |--------------------------------------------------------------------------
                        */

                        $q->whereNotIn(
                            'status',
                            ['cancelled']
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | DATE DÉBUT
                        |--------------------------------------------------------------------------
                        */

                        if ($dateFrom) {
                            $q->whereDate(
                                'created_at',
                                '>=',
                                $dateFrom
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | DATE FIN
                        |--------------------------------------------------------------------------
                        */

                        if ($dateTo) {
                            $q->whereDate(
                                'created_at',
                                '<=',
                                $dateTo
                            );
                        }
                    }
                );
            },
        ], 'quantity')
        ->having(
            'sold_quantity',
            '>',
            0
        );

        /*
        |--------------------------------------------------------------------------
        | RECHERCHE
        |--------------------------------------------------------------------------
        */

        if ($this->search) {
            $search = $this->search;

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'designation',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'reference',
                        'like',
                        '%' . $search . '%'
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXPORT COMPLET
        |--------------------------------------------------------------------------
        */

        return $query
            ->latest()
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | EN-TÊTES
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'Référence',
            'Désignation',
            'Marque',
            'Modèle',
            'Quantité vendue',
            'Unité',
            'Prix achat',
            'Prix vente',
            'Statut',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPING
    |--------------------------------------------------------------------------
    */

    public function map($product): array
    {
        $unitType = strtolower(
            trim(
                (string) (
                    $product->unit_type
                    ?? 'piece'
                )
            )
        );

        $unitLabel = $unitType === 'litre'
            ? 'L'
            : 'Pièce';

        return [
            $product->reference,

            $product->designation,

            $product->brand?->name
                ?? 'Non défini',

            $product->model?->name
                ?? 'Non défini',

            (float) (
                $product->sold_quantity
                ?? 0
            ),

            $unitLabel,

            (float) (
                $product->purchase_price
                ?? 0
            ),

            (float) (
                $product->sale_price
                ?? 0
            ),

            'Vendu',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STYLE
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}
