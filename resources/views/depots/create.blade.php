@extends('layouts.layoutMaster')

@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Nouveau dépôt</h4>
    </div>

    <div class="card-body">
        <form action="{{ route('depots.store') }}" method="POST">
            @csrf

            @include('depots.form')
        </form>
    </div>
</div>

@endsection
