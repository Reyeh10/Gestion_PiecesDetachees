@extends('layouts.layoutMaster')

@section('content')
<div class="container-fluid">

    <h4>Ajouter une pièce détachée</h4>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('products.store') }}" method="POST">
                @csrf

                @include('products.form')

                <button type="submit" class="btn btn-primary mt-3">
                    Enregistrer
                </button>

                <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">
                    Retour
                </a>
            </form>

        </div>
    </div>

</div>
@endsection
