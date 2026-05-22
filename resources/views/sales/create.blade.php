@extends('layouts.layoutMaster')

@section('content')

<form action="{{ route('sales.store') }}" method="POST">

    @csrf

    @include('sales.form')

</form>

@endsection
