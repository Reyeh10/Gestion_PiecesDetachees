<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryAdjustmentTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Référence',
            'Quantité comptée',
            'Rayon',
            'Emplacement',
            'Raison',
        ];
    }

    public function array(): array
    {
        return [
            ['REF-001', 10, 'A', 'A-01', 'Inventaire physique'],
            ['REF-002', 5, '', '', ''],
            ['REF-003', 0, 'B', 'B-04', ''],
        ];
    }
}
