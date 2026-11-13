<?php

namespace App\Exports;

use App\Models\Inventaire;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fiche d'inventaire en Excel (V6) : à remplir (colonnes « Quantité comptée »,
 * « Nouveau rayon », « Nouvel emplacement », « Référence signalée »,
 * « Remarque ») puis à réimporter. La colonne « N° ligne » ne doit pas
 * être modifiée : elle relie chaque ligne à la fiche.
 */
class InventaireExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    public function __construct(protected Inventaire $inventaire)
    {
    }

    public function headings(): array
    {
        return [
            'N° ligne', 'Rayon', 'Emplacement', 'Référence', 'Désignation', 'Quantité attendue',
            'Quantité comptée', 'Nouveau rayon', 'Nouvel emplacement', 'Référence signalée', 'Remarque',
        ];
    }

    public function array(): array
    {
        return $this->inventaire->lignes->map(fn ($l) => [
            $l->id,
            $l->rayon?->name,
            $l->location?->name,
            $l->reference,
            $l->designation,
            (float) $l->qte_attendue,
            $l->qte_comptee !== null ? (float) $l->qte_comptee : null,
            $l->nouveauRayon?->name,
            $l->nouvelEmplacement?->name,
            $l->reference_signalee,
            $l->remarque,
        ])->all();
    }

    public function styles(Worksheet $sheet): array
    {
        // colonnes à remplir en jaune clair
        $fin = count($this->inventaire->lignes) + 1;
        $sheet->getStyle("G2:K{$fin}")->getFill()->setFillType('solid')->getStartColor()->setRGB('FFF7E0');

        return [1 => ['font' => ['bold' => true]]];
    }
}
