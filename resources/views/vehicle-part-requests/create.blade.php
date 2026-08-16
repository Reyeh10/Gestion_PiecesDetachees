@extends('layouts.layoutMaster')

@section('content')

<style>
    .vpr-page {
        width: 100%;
        padding: 22px 18px 45px;
    }

    .vpr-page-inner {
        width: 100%;
        max-width: 1450px;
        margin: 0 auto;
    }

    .vpr-card {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
    }

    .vpr-card-header {
        padding: 24px 28px;
        background: #ffffff;
        border-bottom: 1px solid #edf0f4;
    }

    .vpr-card-header h3 {
        margin: 0 0 5px;
        font-size: 26px;
        font-weight: 800;
        color: #334155;
    }

    .vpr-card-header p {
        margin: 0;
        color: #94a3b8;
    }

    .vpr-card-body {
        padding: 28px;
    }

    .vpr-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 26px;
    }

    .vpr-actions .btn {
        min-width: 125px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 9px;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .vpr-page {
            padding: 14px 10px 32px;
        }

        .vpr-card-header,
        .vpr-card-body {
            padding: 18px 16px;
        }

        .vpr-actions {
            flex-direction: column-reverse;
        }

        .vpr-actions .btn {
            width: 100%;
        }
    }
</style>

<div class="vpr-page">
    <div class="vpr-page-inner">

        <div class="vpr-card">

            <div class="vpr-card-header">
                <h3>
                    Nouvelle commande de pièce
                </h3>

                <p>
                    Rechercher ou commander une pièce pour un véhicule.
                </p>
            </div>

            <div class="vpr-card-body">

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Fermer"
                        ></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>
                            Le formulaire contient des erreurs.
                        </strong>

                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Fermer"
                        ></button>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('vehicle-part-requests.store') }}"
                    autocomplete="off"
                >
                    @csrf

                    @include('vehicle-part-requests._form')

                    <div class="vpr-actions">
                        <a
                            href="{{ route('vehicle-part-requests.index') }}"
                            class="btn btn-secondary"
                        >
                            <i class="bx bx-arrow-back"></i>
                            Annuler
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bx bx-save"></i>
                            Enregistrer
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection