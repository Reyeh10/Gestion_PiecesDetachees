<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Customer::with([
            'sales',
        ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->search) {

            $query->where('name', 'like', '%' . $request->search . '%')
                 ->orWhere('code', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $customers = $query
            ->latest()
            ->paginate(10);

        return view(
            'customers.index',
            compact('customers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('customers.create');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'code' => 'required|string|max:50|unique:customers,code',

            'name' => 'required|string|max:255',

            'phone' => 'nullable|string|max:50',

            'email' => 'nullable|email|max:255|unique:customers,email',

            'credit_limit' => 'nullable|numeric|min:0',

            'payment_terms' => 'nullable|string|max:255',
        ]);

        $customer = Customer::create([

            'code' => $request->code,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'credit_limit' => $request->credit_limit ?? 0,
            'payment_terms' => $request->payment_terms,
        ]);

        /*
        |--------------------------------------------------------------------------
        | AJAX REQUEST
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'customer' => $customer
            ]);
        }

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Client créé avec succès.'
            );
    }
    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Customer $customer)
    {
        $customer->load([

            'sales.items.product',
            'sales.payments',
        ]);

        return view(
            'customers.show',
            compact('customer')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(Customer $customer)
    {
        return view(
            'customers.edit',
            compact('customer')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Customer $customer
    ) {
        $request->validate([
            'code' =>
                'required|string|max:50|unique:customers,code,' .
                $customer->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' =>
                'nullable|email|max:255|unique:customers,email,' .
                $customer->id,

            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            //'address' => 'nullable|string|max:1000',
        ]);

        $customer->update([
            'code' => $request->code,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
           // 'address' => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
            'payment_terms' => $request->payment_terms,
        ]);

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Client modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Customer $customer)
    {
        /*
        |--------------------------------------------------------------------------
        | VERIFICATION VENTES
        |--------------------------------------------------------------------------
        */

        if ($customer->sales()->count() > 0) {

            return redirect()
                ->route('customers.index')
                ->with(
                    'error',
                    'Impossible de supprimer ce client car il possède des ventes.'
                );
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with(
                'success',
                'Client supprimé avec succès.'
            );
    }
}
