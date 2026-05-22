@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">
        <h4>Nouveau client</h4>
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

        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Nom *</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Téléphone</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}">
                </div>

                <!--div class="col-md-6 mb-3">
                    <label>Limite de crédit</label>

                    <input type="number"
                           step="0.01"
                           name="credit_limit"
                           class="form-control"
                           value="{ { old('credit_limit', 0) }}">
                </div-->

                <!--div class="col-md-6 mb-3">
                    <label>Conditions de paiement</label>

                    <input type="text"
                           name="payment_terms"
                           class="form-control"
                           value="{ { old('payment_terms') }}">
                </div-->

                <!--div class="col-md-12 mb-3">
                    <label>Adresse</label>

                    <textarea name="address"
                              class="form-control"
                              rows="3">{ { old('address') }}</textarea>
                </div-->

            </div>

            <div class="text-end">

                <a href="{{ route('customers.index') }}"
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
