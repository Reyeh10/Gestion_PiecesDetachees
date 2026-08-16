@extends('layouts.layoutMaster')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="container-fluid">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-4">
                    <div>
                        <h3 class="mb-1">
                            Modifier la pièce
                        </h3>

                        <p class="text-muted mb-0">
                            {{ $vehiclePartRequest->part_name }}
                        </p>
                    </div>
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

                    <form
                        method="POST"
                        action="{{
                            route(
                                'vehicle-part-requests.update',
                                $vehiclePartRequest
                            )
                        }}"
                    >
                        @csrf
                        @method('PUT')

                        @include(
                            'vehicle-part-requests._form'
                        )

                        <div
                            class="d-flex
                                   justify-content-end
                                   gap-2 mt-4"
                        >
                            <a
                                href="{{
                                    route(
                                        'vehicle-part-requests.show',
                                        $vehiclePartRequest
                                    )
                                }}"
                                class="btn btn-secondary"
                            >
                                Annuler
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
