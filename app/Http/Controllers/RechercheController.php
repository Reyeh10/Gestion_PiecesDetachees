<?php

namespace App\Http\Controllers;

use App\Models\BonTransfert;
use App\Models\Customer;
use App\Models\ExternalBonCommande;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SupplierOrder;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recherche globale de la barre du haut (design DreamsPOS, V6) : pièces,
 * clients, factures, BC garage, BC fournisseurs et BT. Chaque groupe n'est
 * renvoyé que si l'utilisateur peut ouvrir la page du résultat.
 */
class RechercheController extends Controller
{
    private const PAR_GROUPE = 5;

    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['groupes' => []]);
        }

        $user = auth()->user();
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
        $peut = fn (string $route): bool => ($p = PermissionService::forRoute($route)) === null || $user->hasPermission($p);

        $groupes = [];

        if ($peut('products.show')) {
            $groupes[] = [
                'titre' => 'Pièces',
                'items' => Product::query()
                    ->where(fn ($w) => $w->where('reference', 'like', $like)->orWhere('designation', 'like', $like))
                    ->orderBy('designation')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'reference', 'designation', 'quantity'])
                    ->map(fn ($p) => [
                        'titre' => $p->reference,
                        'detail' => $p->designation . ' — stock ' . rtrim(rtrim(number_format((float) $p->quantity, 2, ',', ' '), '0'), ','),
                        'url' => route('products.show', $p),
                    ]),
            ];
        }

        if ($peut('customers.show')) {
            $groupes[] = [
                'titre' => 'Clients',
                'items' => Customer::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('code', 'like', $like))
                    ->orderBy('name')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'code', 'name', 'phone'])
                    ->map(fn ($c) => [
                        'titre' => $c->name,
                        'detail' => trim(($c->code ?? '') . ($c->phone ? ' — ' . $c->phone : '')),
                        'url' => route('customers.show', $c),
                    ]),
            ];
        }

        if ($peut('sales.show')) {
            $groupes[] = [
                'titre' => 'Factures',
                'items' => Sale::query()
                    ->with('customer:id,name')
                    ->where('invoice_number', 'like', $like)
                    ->latest('id')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'invoice_number', 'customer_id', 'total', 'status'])
                    ->map(fn ($s) => [
                        'titre' => $s->invoice_number,
                        'detail' => ($s->customer?->name ?? 'Client') . ' — ' . number_format((float) $s->total, 0, ',', ' '),
                        'url' => route('sales.show', $s),
                    ]),
            ];
        }

        if ($peut('fournisseur-commandes.show')) {
            $groupes[] = [
                'titre' => 'Commandes garage',
                'items' => ExternalBonCommande::query()->garage()
                    ->where(fn ($w) => $w->where('numero', 'like', $like)
                        ->orWhere('client_nom', 'like', $like)
                        ->orWhere('vehicule_immatriculation', 'like', $like))
                    ->latest('id')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'numero', 'client_nom', 'vehicule_marque', 'vehicule_immatriculation'])
                    ->map(fn ($bc) => [
                        'titre' => $bc->numero,
                        'detail' => trim(($bc->client_nom ?? '') . ' — ' . trim(($bc->vehicule_marque ?? '') . ' ' . ($bc->vehicule_immatriculation ?? '')), ' —'),
                        'url' => route('fournisseur-commandes.show', $bc),
                    ]),
            ];
        }

        if ($peut('supplier-orders.show')) {
            $groupes[] = [
                'titre' => 'Bons de commande fournisseur',
                'items' => SupplierOrder::query()
                    ->with('supplier:id,name')
                    ->where('order_number', 'like', $like)
                    ->latest('id')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'order_number', 'supplier_id', 'status'])
                    ->map(fn ($o) => [
                        'titre' => $o->order_number,
                        'detail' => $o->supplier?->name ?? '',
                        'url' => route('supplier-orders.show', $o),
                    ]),
            ];
        }

        if ($peut('bons-transfert.show')) {
            $groupes[] = [
                'titre' => 'Bons de transfert',
                'items' => BonTransfert::query()
                    ->where('numero', 'like', $like)
                    ->latest('id')
                    ->limit(self::PAR_GROUPE)
                    ->get(['id', 'numero', 'total'])
                    ->map(fn ($bt) => [
                        'titre' => $bt->numero,
                        'detail' => 'Total ' . number_format((float) $bt->total, 0, ',', ' '),
                        'url' => route('bons-transfert.show', $bt),
                    ]),
            ];
        }

        return response()->json([
            'groupes' => array_values(array_filter($groupes, fn ($g) => count($g['items']) > 0)),
        ]);
    }
}
