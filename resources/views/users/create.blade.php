@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">

        <h4>Créer utilisateur</h4>

    </div>

    <div class="card-body">

        <form action="{{ route('users.store') }}"
              method="POST">

            @csrf

            @include('users.form')

        </form>

    </div>

</div>

@endsection
