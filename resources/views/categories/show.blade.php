@extends('layouts.layoutMaster')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between">

        <h4>
            Détails famille
        </h4>

        <a href="{{ route('categories.index') }}"
           class="btn btn-secondary">

            Retour

        </a>

    </div>

    <div class="card-body">

        <div class="mb-4">

            <h5>
                Famille
            </h5>

            <p>
                {{ $family->name }}
            </p>

        </div>

        <div>

            <h5>
                Sous-familles
            </h5>

            <ul>

                @forelse($family->subfamilies as $sub)

                    <li>
                        {{ $sub->name }}
                    </li>

                @empty

                    <li>
                        Aucune sous-famille
                    </li>

                @endforelse

            </ul>

        </div>

    </div>

</div>

@endsection
