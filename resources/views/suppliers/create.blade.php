@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">

        <h4>
            Nouveau fournisseur
        </h4>

    </div>

    <div class="card-body">

        <form action="{{ route('suppliers.store') }}"
              method="POST">

            @csrf

            @include('suppliers.form')

            <div class="mt-4 text-end">

                <a href="{{ route('suppliers.index') }}"
                   class="btn btn-secondary">

                    Annuler

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    Enregistrer

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
