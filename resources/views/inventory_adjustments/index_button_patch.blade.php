{{-- Remplacez le bloc d'actions dans resources/views/inventory_adjustments/index.blade.php --}}

@if($canCreateAdjustment)
    <div class="d-flex flex-wrap gap-2">
        <a
            href="{{ route('inventory-adjustments.import') }}"
            class="btn btn-outline-primary"
        >
            <i class="bx bx-spreadsheet me-1"></i>
            Import Excel
        </a>

        <a
            href="{{ route('inventory-adjustments.create') }}"
            class="btn btn-primary"
        >
            <i class="bx bx-plus me-1"></i>
            Nouvel ajustement
        </a>
    </div>
@endif
