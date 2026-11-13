@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">
        <h4>Modifier client</h4>
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

        <form action="{{ route('customers.update', $customer->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Code client *</label>
                    <input type="text"
                        name="code"
                        class="form-control"
                        value="{{ old('code', $customer->code) }}"
                        required>
                </div>
                {{-- NOM --}}
                <div class="col-md-6 mb-3">
                    <label>Nom *</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $customer->name) }}"
                           required>
                </div>

                {{-- TELEPHONE --}}
                <div class="col-md-6 mb-3">
                    <label>Téléphone</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone', $customer->phone) }}">
                </div>

                {{-- EMAIL --}}
                <div class="col-md-6 mb-3">
                    <label>Email</label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email', $customer->email) }}">
                </div>

                {{-- V6 : ASSURANCE (prix des ventes / proformas majorés, comme l'Atelier) --}}
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-md-4">
                        <input type="hidden" name="est_assurance" value="0">
                        <input class="form-check-input" type="checkbox" name="est_assurance" value="1" id="est_assurance"
                               @checked(old('est_assurance', $customer?->est_assurance))
                               onchange="document.getElementById('bloc_taux_majoration').classList.toggle('d-none', !this.checked)">
                        <label class="form-check-label" for="est_assurance">
                            Ce client est une <strong>assurance</strong>
                        </label>
                    </div>
                    <small class="text-muted">Ses ventes et proformas sont majorés (seuls les prix unitaires montent).</small>
                </div>

                <div class="col-md-6 mb-3 {{ old('est_assurance', $customer?->est_assurance) ? '' : 'd-none' }}" id="bloc_taux_majoration">
                    <label for="taux_majoration" class="form-label">Taux de majoration (%)</label>
                    <input type="number" id="taux_majoration" name="taux_majoration" class="form-control" step="0.01" min="0" max="300"
                           value="{{ old('taux_majoration', $customer?->taux_majoration) }}"
                           placeholder="Vide = {{ rtrim(rtrim(number_format(config('magasin.taux_majoration_assurance', 35), 2, ',', ''), '0'), ',') }} % (taux par défaut)">
                </div>

            </div>

            <div class="text-end">

                <a href="{{ route('customers.index') }}"
                   class="btn btn-secondary">
                    Annuler
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    Modifier
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
