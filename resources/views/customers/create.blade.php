@extends('layouts.layoutMaster')

@section('content')

<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">
                Nouveau client
            </h4>

            <small class="text-muted">
                Ajouter un nouveau client
            </small>
        </div>

        <a href="{{ route('customers.index') }}"
           class="btn btn-secondary">

            <i class="bx bx-arrow-back me-1"></i>

            Retour
        </a>

    </div>

    {{-- BODY --}}
    <div class="card-body">

        {{-- ERREURS DE VALIDATION --}}
        @if($errors->any())

            <div class="alert alert-danger">

                <strong>
                    Veuillez corriger les erreurs suivantes :
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- MESSAGE ERROR SESSION --}}
        @if(session('error'))

            <div class="alert alert-danger">

                <i class="bx bx-error-circle me-1"></i>

                {{ session('error') }}

            </div>

        @endif


        {{-- FORMULAIRE --}}
        <form action="{{ route('customers.store') }}"
              method="POST">

            @csrf

            <div class="row">

                {{-- CODE CLIENT --}}
                <div class="col-md-6 mb-3">

                    <label for="code"
                           class="form-label fw-semibold">

                        Code client

                    </label>

                    <input
                        type="text"
                        id="code"
                        class="form-control bg-light"
                        value="{{ $nextCode ?? 'CL001' }}"
                        readonly
                    >

                    <small class="text-muted">

                        <i class="bx bx-info-circle me-1"></i>

                        Le code est généré automatiquement par le système.

                    </small>

                </div>


                {{-- NOM --}}
                <div class="col-md-6 mb-3">

                    <label for="name"
                           class="form-label fw-semibold">

                        Nom

                        <span class="text-danger">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Nom du client"
                        required
                        autofocus
                    >

                    @error('name')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- TELEPHONE --}}
                <div class="col-md-6 mb-3">

                    <label for="phone"
                           class="form-label fw-semibold">

                        Téléphone

                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        placeholder="Ex : 77 88 99 00"
                    >

                    @error('phone')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- EMAIL --}}
                <div class="col-md-6 mb-3">

                    <label for="email"
                           class="form-label fw-semibold">

                        Email

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="Ex : client@email.com"
                    >

                    @error('email')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- V6 : ASSURANCE (prix des ventes / proformas majorés, comme l'Atelier) --}}
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-md-4">
                        <input type="hidden" name="est_assurance" value="0">
                        <input class="form-check-input" type="checkbox" name="est_assurance" value="1" id="est_assurance"
                               @checked(old('est_assurance', null?->est_assurance))
                               onchange="document.getElementById('bloc_taux_majoration').classList.toggle('d-none', !this.checked)">
                        <label class="form-check-label" for="est_assurance">
                            Ce client est une <strong>assurance</strong>
                        </label>
                    </div>
                    <small class="text-muted">Ses ventes et proformas sont majorés (seuls les prix unitaires montent).</small>
                </div>

                <div class="col-md-6 mb-3 {{ old('est_assurance', null?->est_assurance) ? '' : 'd-none' }}" id="bloc_taux_majoration">
                    <label for="taux_majoration" class="form-label">Taux de majoration (%)</label>
                    <input type="number" id="taux_majoration" name="taux_majoration" class="form-control" step="0.01" min="0" max="300"
                           value="{{ old('taux_majoration', null?->taux_majoration) }}"
                           placeholder="Vide = {{ rtrim(rtrim(number_format(config('magasin.taux_majoration_assurance', 35), 2, ',', ''), '0'), ',') }} % (taux par défaut)">
                </div>

                {{-- LIMITE DE CREDIT --}}
                {{--
                <div class="col-md-6 mb-3">

                    <label for="credit_limit"
                           class="form-label">

                        Limite de crédit

                    </label>

                    <input
                        type="number"
                        id="credit_limit"
                        name="credit_limit"
                        class="form-control"
                        step="0.01"
                        min="0"
                        value="{{ old('credit_limit', 0) }}"
                    >

                </div>
                --}}


                {{-- CONDITIONS DE PAIEMENT --}}
                {{--
                <div class="col-md-6 mb-3">

                    <label for="payment_terms"
                           class="form-label">

                        Conditions de paiement

                    </label>

                    <input
                        type="text"
                        id="payment_terms"
                        name="payment_terms"
                        class="form-control"
                        value="{{ old('payment_terms') }}"
                    >

                </div>
                --}}


                {{-- ADRESSE --}}
                {{--
                <div class="col-md-12 mb-3">

                    <label for="address"
                           class="form-label">

                        Adresse

                    </label>

                    <textarea
                        id="address"
                        name="address"
                        class="form-control"
                        rows="3"
                    >{{ old('address') }}</textarea>

                </div>
                --}}

            </div>


            {{-- ACTIONS --}}
            <div class="d-flex justify-content-end gap-2 mt-3">

                <a href="{{ route('customers.index') }}"
                   class="btn btn-secondary">

                    <i class="bx bx-x me-1"></i>

                    Annuler

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    <i class="bx bx-save me-1"></i>

                    Enregistrer

                </button>

            </div>

        </form>

    </div>

</div>

@endsection
