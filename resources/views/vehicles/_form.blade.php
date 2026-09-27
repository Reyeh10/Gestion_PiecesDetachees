<div class="row g-3">

    {{-- CLIENT --}}

    <div class="col-md-6">
        <label for="vehicle_customer_id" class="form-label fw-semibold">
            Client propriétaire
        </label>
        <select id="vehicle_customer_id" name="customer_id" class="form-select select2">
            <option value="">Aucun client sélectionné</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $vehicle->customer_id ?? '') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- IMMATRICULATION --}}

    <div class="col-md-6">
        <label for="vehicle_plate_number" class="form-label fw-semibold">
            Immatriculation <span class="text-danger">*</span>
        </label>
        <input
            id="vehicle_plate_number"
            type="text"
            name="plate_number"
            value="{{ old('plate_number', $vehicle->plate_number ?? '') }}"
            class="form-control text-uppercase"
            placeholder="Exemple : 336D106"
            maxlength="50" required
            oninput="formatVehiclePlate(this)"
        >
        @error('plate_number')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- VIN --}}

    <div class="col-md-6">
        <label for="vehicle_vin" class="form-label fw-semibold">
            Numéro VIN
        </label>
        <input
            id="vehicle_vin"
            type="text"
            name="vin"
            value="{{ old('vin', $vehicle->vin ?? '') }}"
            class="form-control text-uppercase"
            placeholder="Numéro de châssis"
            maxlength="100"
        >
        @error('vin')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- MARQUE --}}

    <div class="col-md-6">
        <label for="vehicle_brand" class="form-label fw-semibold">
            Marque
        </label>
        <input
            id="vehicle_brand"
            type="text"
            name="brand"
            value="{{ old('brand', $vehicle->brand ?? '') }}"
            class="form-control"
            placeholder="Exemple : Toyota"
            maxlength="100"
        >
        @error('brand')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- MODÈLE --}}

    <div class="col-md-4">
        <label for="vehicle_model" class="form-label fw-semibold">
            Modèle
        </label>
        <input
            id="vehicle_model"
            type="text"
            name="model"
            value="{{ old('model', $vehicle->model ?? '') }}"
            class="form-control"
            placeholder="Exemple : Hilux"
            maxlength="100"
        >
        @error('model')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- ANNÉE --}}

    <div class="col-md-4">
        <label for="vehicle_year" class="form-label fw-semibold">
            Année
        </label>
        <input
            id="vehicle_year"
            type="number"
            name="year"
            value="{{ old('year', $vehicle->year ?? '') }}"
            class="form-control"
            placeholder="{{ date('Y') }}"
            min="1900"
            max="{{ date('Y') + 1 }}"
        >
        @error('year')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- COULEUR --}}

    <div class="col-md-4">
        <label for="vehicle_color" class="form-label fw-semibold">
            Couleur
        </label>
        <input
            id="vehicle_color"
            type="text"
            name="color"
            value="{{ old('color', $vehicle->color ?? '') }}"
            class="form-control"
            placeholder="Exemple : Blanc"
            maxlength="100"
        >
        @error('color')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- NOTES --}}

    <div class="col-12">
        <label for="vehicle_notes" class="form-label fw-semibold">
            Notes
        </label>
        <textarea id="vehicle_notes" name="notes" class="form-control" rows="4" maxlength="2000" placeholder="Informations complémentaires sur le véhicule">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
        @error('notes')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Mise en forme de l’immatriculation pendant la saisie. --}}
<script>
function formatVehiclePlate(input) {
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
}
</script>
