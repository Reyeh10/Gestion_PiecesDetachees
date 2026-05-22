@extends('layouts.layoutMaster')

@section('content')

{{-- SUCCESS --}}
@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif

{{-- ERROR --}}
@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show">

        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif

{{-- VALIDATION ERRORS --}}
@if ($errors->any())

    <div class="alert alert-danger">

        <strong>
            Erreurs détectées :
        </strong>

        <ul class="mb-0 mt-2">

            @foreach ($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif

<form action="{{ route('proformas.store') }}"
      method="POST">

    @csrf

    @include('proformas.form')

</form>

@endsection
