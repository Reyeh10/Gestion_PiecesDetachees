@extends('layouts.layoutMaster')

@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Modifier le dépôt</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('depots.update', $depot) }}" method="POST">
            @csrf
            @method('PUT')

            @include('depots.form')
        </form>
    </div>
</div>

@endsection
