<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::get()->map(function ($product) {

            return [

                'reference'     => $product->reference,

                'designation'   => $product->designation,

                //'depot'         => $product->depot->name ?? '-',

                'quantity'      => $product->quantity,

                'cost_price'    => $product->cost_price,

                'sale_price'    => $product->sale_price,

            ];
        });
    }

    public function headings(): array
    {
        return [

            'Référence',
            'Désignation',
           // 'Dépôt',
            'Quantité',
            'Prix stock',
            'Prix vente',

        ];
    }
}
