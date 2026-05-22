@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('categories.store') }}"
      method="POST">

    @csrf

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h4>
                Nouvelle famille
            </h4>

            <a href="{{ route('categories.index') }}"
               class="btn btn-secondary">

                Retour

            </a>

        </div>

        <div class="card-body">

            @include('categories.form')

        </div>

        <div class="card-footer text-end">

            <button type="submit"
                    class="btn btn-primary">

                Enregistrer

            </button>

        </div>

    </div>

</form>

@endsection
