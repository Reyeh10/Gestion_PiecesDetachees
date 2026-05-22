@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">

        <h4>
            Modifier fournisseur
        </h4>

    </div>

    <div class="card-body">

        <form action="{{ route('suppliers.update', $supplier->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            @include('suppliers.form')

            <div class="mt-4 text-end">

                <a href="{{ route('suppliers.index') }}"
                   class="btn btn-secondary">

                    Annuler

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    Mettre à jour

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
