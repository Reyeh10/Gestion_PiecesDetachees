@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('purchases.update', $purchase->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-1">
                    Modifier achat
                </h4>
            </div>

            <a href="{{ route('purchases.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

        <div class="card-body">

            @include('purchases.form')

        </div>

    </div>

</form>

@endsection
