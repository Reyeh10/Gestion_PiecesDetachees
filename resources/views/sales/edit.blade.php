@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('sales.update', $sale) }}" method="POST">

    @csrf
    @method('PUT')

    @include('sales.form')

</form>

@endsection
