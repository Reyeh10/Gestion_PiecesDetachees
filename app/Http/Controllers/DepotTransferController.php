<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\Product;
use App\Models\ProductDepotStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepotTransferController extends Controller
{
    public function index()
    {
        $transfers = DepotTransfer::with([
            'product',
            'sourceDepot',
            'destinationDepot',
            'user',
        ])->latest()->get();

        return view('depot_transfers.index', compact('transfers'));
    }

    public function create()
    {
        $products = Product::orderBy('designation')->get();

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('depot_transfers.create', compact('products', 'depots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'source_depot_id' => 'required|exists:depots,id',
            'destination_depot_id' => 'required|exists:depots,id|different:source_depot_id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $sourceStock = ProductDepotStock::with(['product', 'depot'])
            ->where('product_id', $request->product_id)
            ->where('depot_id', $request->source_depot_id)
            ->first();

        if (!$sourceStock || $sourceStock->quantity <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ce produit n’existe pas dans le dépôt source ou son stock est nul.');
        }

        if ($sourceStock->quantity < $request->quantity) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Stock insuffisant. Quantité disponible dans le dépôt source : '
                    . $sourceStock->quantity
                    . '. Quantité demandée : '
                    . $request->quantity
                    . '.'
                );
        }

        DB::transaction(function () use ($request, $sourceStock) {

            $destinationStock = ProductDepotStock::firstOrCreate(
                [
                    'product_id' => $request->product_id,
                    'depot_id' => $request->destination_depot_id,
                ],
                [
                    'quantity' => 0,
                ]
            );

            $sourceStock->quantity -= $request->quantity;
            $destinationStock->quantity += $request->quantity;

            $destinationStock->save();

            if ($sourceStock->quantity <= 0) {
                $sourceStock->delete();
            } else {
                $sourceStock->save();
            }

            DepotTransfer::create([
                'product_id' => $request->product_id,
                'source_depot_id' => $request->source_depot_id,
                'destination_depot_id' => $request->destination_depot_id,
                'quantity' => $request->quantity,
                'note' => $request->note,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('depot-transfers.index')
            ->with('success', 'Transfert effectué avec succès.');
    }
    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(DepotTransfer $transfer)
    {
        return view(
            'depot_transfers.show',
            compact('transfer')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(DepotTransfer $transfer)
    {
        $products = Product::orderBy('designation')->get();

        $depots = Depot::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'depot_transfers.edit',
            compact(
                'transfer',
                'products',
                'depots'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        DepotTransfer $transfer
    ) {

        $request->validate([

            'note' => 'nullable|string',

        ]);

        $transfer->update([

            'note' => $request->note,

        ]);

        return redirect()
            ->route('depot-transfers.index')
            ->with(
                'success',
                'Transfert modifié avec succès.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(DepotTransfer $transfer)
    {
        $transfer->delete();

        return redirect()
            ->route('depot-transfers.index')
            ->with(
                'success',
                'Transfert supprimé avec succès.'
            );
    }
}
