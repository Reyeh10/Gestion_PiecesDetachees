@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('categories.update', $category) }}"
      method="POST">

    @csrf
    @method('PUT')

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h4>
                Modifier famille
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
                    class="btn btn-warning">

                Modifier

            </button>

        </div>

    </div>

</form>

@endsection
