@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">

        <h4>Modifier utilisateur</h4>

    </div>

    <div class="card-body">

        <form action="{{ route('users.update', $user->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            @include('users.form')

        </form>

    </div>

</div>

@endsection
