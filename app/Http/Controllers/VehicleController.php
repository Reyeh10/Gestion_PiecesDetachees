<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class VehicleController extends Controller
{
    /**
     * Afficher la liste des véhicules.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $vehicles = Vehicle::query()
            ->with('customer')
           ->withCount('sales')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $normalizedPlate =
                        $this->normalizePlate($search);

                    $query->where(
                        function ($vehicleQuery) use (
                            $search,
                            $normalizedPlate
                        ) {
                            $vehicleQuery
                                ->where(
                                    'plate_number',
                                    'like',
                                    '%' . $normalizedPlate . '%'
                                )
                                ->orWhere(
                                    'vin',
                                    'like',
                                    '%' . strtoupper($search) . '%'
                                )
                                ->orWhere(
                                    'brand',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'model',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'color',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhereHas(
                                    'customer',
                                    function ($customerQuery) use ($search) {
                                        $customerQuery->where(
                                            'name',
                                            'like',
                                            '%' . $search . '%'
                                        );
                                    }
                                );
                        }
                    );
                }
            )
            ->orderBy('plate_number')
            ->paginate(15)
            ->withQueryString();

        return view(
            'vehicles.index',
            compact(
                'vehicles',
                'search'
            )
        );
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'vehicles.create',
            compact('customers')
        );
    }

    /**
     * Enregistrer un véhicule.
     */
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | NORMALISATION AVANT VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'plate_number' => $this->normalizePlate(
                (string) $request->input('plate_number')
            ),

            'vin' => $this->normalizeVin(
                $request->input('vin')
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'customer_id' => [
                    'nullable',
                    'integer',
                    'exists:customers,id',
                ],

                'plate_number' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:vehicles,plate_number',
                ],

                'vin' => [
                    'nullable',
                    'string',
                    'max:100',
                    'unique:vehicles,vin',
                ],

                'brand' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'model' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'year' => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 1),
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'plate_number.required' =>
                    'L’immatriculation est obligatoire.',

                'plate_number.unique' =>
                    'Cette immatriculation existe déjà.',

                'plate_number.max' =>
                    'L’immatriculation ne doit pas dépasser 50 caractères.',

                'vin.unique' =>
                    'Ce numéro VIN existe déjà.',

                'vin.max' =>
                    'Le numéro VIN ne doit pas dépasser 100 caractères.',

                'customer_id.exists' =>
                    'Le client sélectionné est invalide.',

                'year.integer' =>
                    'L’année doit être un nombre valide.',

                'year.min' =>
                    'L’année ne peut pas être inférieure à 1900.',

                'year.max' =>
                    'L’année saisie est invalide.',
            ]
        );

        DB::beginTransaction();

        try {
            $vehicle = Vehicle::create($validated);

            DB::commit();

            return redirect()
                ->route('vehicles.show', $vehicle)
                ->with(
                    'success',
                    'Véhicule ajouté avec succès.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Impossible d’ajouter le véhicule. Veuillez réessayer.'
                );
        }
    }

    /**
     * Afficher un véhicule.
     */
   public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'customer',

            'sales' => function ($query) {
                $query
                    ->with([
                        'customer',
                        'payments',
                        'items.product.brand',
                        'items.product.model',
                    ])
                    ->where(
                        'document_type',
                        'sale'
                    )
                    ->latest();
            },

            'partRequests' => function ($query) {
            $query->latest('requested_at')
                ->latest('id');
        },
        
        ]);

        return view(
            'vehicles.show',
            compact('vehicle')
        );
    }

    /**
     * Afficher le formulaire de modification.
     */
    public function edit(Vehicle $vehicle): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        return view(
            'vehicles.edit',
            compact(
                'vehicle',
                'customers'
            )
        );
    }

    /**
     * Modifier un véhicule.
     */
    public function update(
        Request $request,
        Vehicle $vehicle
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | NORMALISATION AVANT VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'plate_number' => $this->normalizePlate(
                (string) $request->input('plate_number')
            ),

            'vin' => $this->normalizeVin(
                $request->input('vin')
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'customer_id' => [
                    'nullable',
                    'integer',
                    'exists:customers,id',
                ],

                'plate_number' => [
                    'required',
                    'string',
                    'max:50',

                    Rule::unique(
                        'vehicles',
                        'plate_number'
                    )->ignore($vehicle->id),
                ],

                'vin' => [
                    'nullable',
                    'string',
                    'max:100',

                    Rule::unique(
                        'vehicles',
                        'vin'
                    )->ignore($vehicle->id),
                ],

                'brand' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'model' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'year' => [
                    'nullable',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 1),
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [
                'plate_number.required' =>
                    'L’immatriculation est obligatoire.',

                'plate_number.unique' =>
                    'Cette immatriculation existe déjà.',

                'vin.unique' =>
                    'Ce numéro VIN existe déjà.',

                'customer_id.exists' =>
                    'Le client sélectionné est invalide.',

                'year.integer' =>
                    'L’année doit être un nombre valide.',
            ]
        );

        DB::beginTransaction();

        try {
            $vehicle->update($validated);

            DB::commit();

            return redirect()
                ->route('vehicles.show', $vehicle)
                ->with(
                    'success',
                    'Véhicule modifié avec succès.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Impossible de modifier le véhicule.'
                );
        }
    }

    /**
     * Supprimer un véhicule.
     *
     * Seul l'administrateur peut supprimer.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | CONTRÔLE DU RÔLE
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->check() ||
            auth()->user()->role !== 'admin'
        ) {
            abort(
                403,
                'Seul l’administrateur peut supprimer un véhicule.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PROTECTION DE L'HISTORIQUE
        |--------------------------------------------------------------------------
        |
        | Un véhicule ayant déjà été utilisé dans une vente ne doit pas être
        | supprimé. Cela préservera l'historique des factures et des pièces.
        |
        */

       if ($vehicle->sales()->exists()) {
            return redirect()
                ->route('vehicles.index')
                ->with(
                    'error',
                    'Ce véhicule ne peut pas être supprimé, car il est lié à une ou plusieurs ventes.'
                );
        }

        DB::beginTransaction();

        try {
            $vehicle->delete();

            DB::commit();

            return redirect()
                ->route('vehicles.index')
                ->with(
                    'success',
                    'Véhicule supprimé avec succès.'
                );
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return redirect()
                ->route('vehicles.index')
                ->with(
                    'error',
                    'Impossible de supprimer le véhicule.'
                );
        }
    }

    /**
     * Normaliser une immatriculation.
     *
     * Exemple :
     * 336 d 106 devient 336D106.
     */
    private function normalizePlate(string $plate): string
    {
        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                trim($plate)
            ) ?? ''
        );
    }

    /**
     * Normaliser le VIN.
     */
    private function normalizeVin(mixed $vin): ?string
    {
        if (
            $vin === null ||
            trim((string) $vin) === ''
        ) {
            return null;
        }

        return strtoupper(
            preg_replace(
                '/\s+/',
                '',
                trim((string) $vin)
            ) ?? ''
        );
    }
}
