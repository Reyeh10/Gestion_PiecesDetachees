@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('proformas.update', $sale) }}" method="POST">

    @csrf
    @method('PUT')

    @include('proformas.form')

</form>

@endsection
