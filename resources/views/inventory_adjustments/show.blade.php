@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('inventory-adjustments.store') }}"
      method="POST">

    @csrf

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                Nouvel ajustement inventaire
            </h4>

            <a href="{{ route('inventory-adjustments.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

        <div class="card-body">

            @include('inventory_adjustments.form')

        </div>

        <div class="card-footer text-end">

            <button type="submit"
                    class="btn btn-primary">

                Enregistrer ajustement

            </button>

        </div>

    </div>

</form>

@endsection
