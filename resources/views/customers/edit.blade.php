@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header">
        <h4>Modifier client</h4>
    </div>

    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customers.update', $customer->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label>Code client *</label>
                    <input type="text"
                        name="code"
                        class="form-control"
                        value="{{ old('code', $customer->code) }}"
                        required>
                </div>
                {{-- NOM --}}
                <div class="col-md-6 mb-3">
                    <label>Nom *</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $customer->name) }}"
                           required>
                </div>

                {{-- TELEPHONE --}}
                <div class="col-md-6 mb-3">
                    <label>Téléphone</label>

                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone', $customer->phone) }}">
                </div>

                {{-- EMAIL --}}
                <div class="col-md-6 mb-3">
                    <label>Email</label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email', $customer->email) }}">
                </div>

            </div>

            <div class="text-end">

                <a href="{{ route('customers.index') }}"
                   class="btn btn-secondary">
                    Annuler
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    Modifier
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
