@extends('layouts.layoutMaster')

@section('content')
<div class="container-fluid">

    <h4>Modifier la pièce</h4>

    <div class="card mt-3">
        <div class="card-body">

            <form action="{{ route('products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('products.form')

                <button type="submit" class="btn btn-primary mt-3">
                    Mettre à jour
                </button>

                <a href="{{ route('products.index') }}" class="btn btn-secondary mt-3">
                    Retour
                </a>
            </form>

        </div>
    </div>

</div>
@endsection
