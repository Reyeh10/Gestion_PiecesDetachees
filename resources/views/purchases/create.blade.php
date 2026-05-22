@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('purchases.store') }}"
      method="POST">

    @csrf

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-1">
                    Générer un achat
                </h4>

                <small>
                    Créer un nouvel achat fournisseur
                </small>

            </div>

            <a href="{{ route('purchases.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

        <div class="card-body">

            @if($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            {{-- FORMULAIRE --}}
            @include('purchases.form')

        </div>

    </div>

</form>

@endsection
