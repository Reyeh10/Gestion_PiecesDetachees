<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel de la page « Pièces à commander » : mêmes pièces, même
 * recherche et même statut que la page (la liste est préparée par
 * ProductController::exportToOrderExcel).
 */
class ProductsToOrderExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithStrictNullComparison
{
    public function __construct(
        protected Collection $products
    ) {
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'Référence',
            'Désignation',
            'Marque',
            'Modèle',
            'Quantité disponible',
            'Stock minimum',
            'Unité',
            'Statut',
        ];
    }

    public function map($product): array
    {
        $disponible = (float) $product->quantity;
        $minimum = (float) $product->min_stock;

        return [
            $product->reference,
            $product->designation,
            $product->brand?->name ?? 'Non définie',
            $product->model?->name ?? 'Non défini',
            $disponible,
            $minimum,
            $product->unit_label,
            $this->statut($product, $disponible, $minimum),
        ];
    }

    /**
     * Même ordre de priorité que la colonne STATUT de la page.
     */
    protected function statut($product, float $disponible, float $minimum): string
    {
        $initial = (float) ($product->initial_quantity ?? 0);
        $recu = (float) ($product->received_quantity ?? 0);

        return match (true) {
            $product->supply_status === 'en_recherche' => 'En recherche',
            $product->supply_status === 'en_commande' => 'En commande',
            $recu > 0 && $recu < $initial => 'Partiellement reçu',
            $disponible <= 0 => 'Rupture',
            $minimum > 0 && $disponible <= $minimum => 'Stock faible',
            default => 'À traiter',
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
