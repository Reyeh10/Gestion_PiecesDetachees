@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Détails achat
        </h4>

        <a href="{{ route('purchases.index') }}"
           class="btn btn-secondary">

            Retour

        </a>

    </div>

    <div class="card-body">

        {{-- FOURNISSEUR --}}
        <div class="row mb-4">

            <div class="col-md-6">

                <h6 class="text-muted">
                    Fournisseur
                </h6>

                <h5>
                    {{ $purchase->supplier->name ?? '-' }}
                </h5>

            </div>

            <div class="col-md-6 text-end">

                <h6 class="text-muted">
                    Date
                </h6>

                <h5>
                    {{ $purchase->created_at->format('d/m/Y H:i') }}
                </h5>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead>

                    <tr>

                        <th>Référence</th>

                        <th>Désignation</th>

                        <th>Prix unitaire achat</th>

                        <th>Quantité</th>

                        <th>Total</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($purchase->items as $item)

                        <tr>

                            <td>
                                {{ $item->product->reference ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product->designation ?? '-' }}
                            </td>

                            <td>
                                {{ number_format($item->price, 2, ',', ' ') }} $
                            </td>

                            <td>
                                {{ $item->quantity }}
                            </td>

                            <td>

                                {{ number_format(
                                    $item->price * $item->quantity,
                                    2,
                                    ',',
                                    ' '
                                ) }} $

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        {{-- TOTAL --}}
        <div class="text-end mt-4">

            <h4>

                Total achat :
                <strong>

                    {{ number_format($purchase->total, 2, ',', ' ') }} $

                </strong>

            </h4>

        </div>

    </div>

</div>

@endsection
