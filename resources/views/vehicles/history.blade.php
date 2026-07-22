@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    <div class="card-header border-0">

        <h3 class="mb-1 fw-bold">
            Traçabilité par immatriculation
        </h3>

        <p class="text-muted mb-0">
            Recherchez toutes les pièces vendues pour un véhicule.
        </p>

    </div>

    <div class="card-body">

        {{-- FORMULAIRE DE RECHERCHE --}}
        <form
            method="GET"
            action="{{ route('vehicles.history') }}"
            class="row g-3 align-items-end mb-4"
        >

            <div class="col-md-8">

                <label class="form-label fw-semibold">
                    Immatriculation
                </label>

                <input
                    type="text"
                    name="plate"
                    value="{{ $plate }}"
                    class="form-control text-uppercase"
                    placeholder="Exemple : 336D106"
                    autocomplete="off"
                    required
                >

            </div>

            <div class="col-md-4">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bx bx-search me-1"></i>
                    Rechercher
                </button>

                <a
                    href="{{ route('vehicles.history') }}"
                    class="btn btn-outline-secondary"
                >
                    Réinitialiser
                </a>

            </div>

        </form>

        @if($plate !== '')

            <div class="alert alert-info">

                Résultats pour l’immatriculation :

                <strong>
                    {{ $plate }}
                </strong>

            </div>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>Date</th>
                            <th>Facture</th>
                            <th>Client</th>
                            <th>Référence</th>
                            <th>Désignation</th>
                            <th>
                                Immatriculation
                            </th>
                            <th class="text-center">
                                Quantité
                            </th>
                            <th class="text-center">
                                Statut
                            </th>
                            <th class="text-center">
                                Action
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($items as $item)

                            <tr>

                                <td>
                                    {{ optional(
                                        $item->sale->created_at
                                    )->format('d/m/Y') }}
                                </td>

                                <td class="fw-bold">
                                    {{ $item->sale->invoice_number ?? '-' }}
                                </td>

                                <td>
                                    {{
                                        $item->sale->customer->name
                                        ?? 'Vente comptoir'
                                    }}
                                </td>

                                <td>
                                    {{ $item->product->reference ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->product->designation ?? '-' }}
                                </td>

                                <td class="fw-bold">
                                    {{ $item->vehicle?->plate_number ?? '-' }}
                                </td>

                                <td class="text-center">

                                    {{ $item->quantity }}

                                    {{
                                        $item->product->unit_label
                                        ?? 'Pièce'
                                    }}

                                </td>

                                <td class="text-center">

                                    @if($item->sale->status === 'cancelled')

                                        <span class="badge bg-danger">
                                            Annulée
                                        </span>

                                    @elseif($item->sale->status === 'payé')

                                        <span class="badge bg-success">
                                            Payée
                                        </span>

                                    @else

                                        <span class="badge bg-primary">
                                            {{ ucfirst($item->sale->status) }}
                                        </span>

                                    @endif

                                </td>

                                <td class="text-center">

                                    <a
                                        href="{{ route(
                                            'sales.show',
                                            $item->sale
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Voir facture
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center py-5 text-muted"
                                >
                                    Aucune pièce trouvée pour cette immatriculation.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection
