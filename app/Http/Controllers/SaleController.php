<?php

namespace App\Http\Controllers;

use App\Models\Avoir;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductDepotStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use NumberToWords\NumberToWords;

class SaleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
   public function index(Request $request)
    {
        // V6 : mêmes filtres que l'Atelier (recherche, statut, période, payeur)
        $filtres = $this->ventesFiltrees($request);

        // Chiffres de la sélection (les factures annulées n'ont pas de TVA due)
        $actives = (clone $filtres)->where('status', '!=', 'cancelled');
        $resume = [
            'nombre' => (clone $filtres)->count(),
            'ttc'    => (float) (clone $actives)->sum('total'),
            'tva'    => (float) (clone $actives)->sum('tva'),
            'reste'  => 0.0,
        ];

        $nonPayees = (clone $actives)->where('status', '!=', 'payé');
        $resume['reste'] = max(0, (float) (clone $nonPayees)->sum('total')
            - (float) Payment::query()->whereIn('sale_id', (clone $nonPayees)->select('id'))->sum('amount'));

        $sales = $filtres
            ->with([
                'customer',
                'vehicle',
                'items',
                'payments',
            ])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view(
            'sales.index',
            compact('sales', 'resume')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | V6 : FACTURES FILTRÉES (liste et export Excel)
    |--------------------------------------------------------------------------
    |
    | recherche : n° facture, client (nom, téléphone), immatriculation,
    |             référence ou désignation d'une pièce
    | statut    : non_payees | payees | annulees
    | periode   : jour | mois | annee, avec date_ref (2026-11-12 / 2026-11 / 2026)
    | payeur    : assurance | client
    |
    */
    private function ventesFiltrees(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Sale::query()
            ->where('document_type', 'sale')
            ->where('invoice_number', 'NOT LIKE', 'PRO-%');

        // l'ancien champ « client » de la liste reste accepté
        $recherche = trim((string) ($request->input('recherche') ?? $request->input('client', '')));

        if ($recherche !== '') {
            $immat = strtoupper(preg_replace('/[\s\-\.]+/', '', $recherche));

            $query->where(function ($q) use ($recherche, $immat) {
                $q->where('invoice_number', 'like', "%{$recherche}%")
                    ->orWhereRaw("REPLACE(REPLACE(UPPER(COALESCE(immatriculation, '')), ' ', ''), '-', '') LIKE ?", ["%{$immat}%"])
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$recherche}%")
                        ->orWhere('phone', 'like', "%{$recherche}%"))
                    ->orWhereHas('vehicle', fn ($v) => $v->whereRaw("REPLACE(REPLACE(UPPER(plate_number), ' ', ''), '-', '') LIKE ?", ["%{$immat}%"]))
                    ->orWhereHas('items.product', fn ($p) => $p->where('reference', 'like', "%{$recherche}%")
                        ->orWhere('designation', 'like', "%{$recherche}%"));
            });
        }

        match ($request->input('statut')) {
            'non_payees' => $query->whereIn('status', ['vendu', 'partiel']),
            'payees'     => $query->where('status', 'payé'),
            'annulees'   => $query->where('status', 'cancelled'),
            default      => null,
        };

        match ($request->input('payeur')) {
            'assurance' => $query->whereHas('customer', fn ($c) => $c->where('est_assurance', true)),
            'client'    => $query->where(fn ($q) => $q->whereDoesntHave('customer')
                ->orWhereHas('customer', fn ($c) => $c->where(fn ($w) => $w->where('est_assurance', false)->orWhereNull('est_assurance')))),
            default     => null,
        };

        // ancien filtre « date » de la liste
        if ($request->filled('date') && ! $request->filled('periode')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        if ($date = $this->dateDePeriode($request)) {
            match ($request->input('periode')) {
                'jour'  => $query->whereDate('created_at', $date->toDateString()),
                'mois'  => $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month),
                'annee' => $query->whereYear('created_at', $date->year),
                default => null,
            };
        }

        return $query;
    }

    /** Date de référence de la période (« 2026 » seul serait lu comme une heure : on complète). */
    private function dateDePeriode(Request $request): ?\Carbon\Carbon
    {
        $periode = $request->input('periode');

        if (! in_array($periode, ['jour', 'mois', 'annee'], true)) {
            return null;
        }

        $ref = trim((string) $request->input('date_ref', ''));

        try {
            return $ref !== ''
                ? \Carbon\Carbon::parse($ref . ($periode === 'annee' ? '-01-01' : ($periode === 'mois' ? '-01' : '')))
                : now();
        } catch (\Throwable $e) {
            return now();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | V6 : FICHE DE SUIVI DE RÈGLEMENT (EXCEL) — RÉCOLTE DE LA TVA
    |--------------------------------------------------------------------------
    |
    | Même présentation que l'Atelier. Mêmes filtres que la liste. Les factures
    | annulées n'y sont pas (leur TVA n'est pas due), sauf avec le filtre
    | « Annulées ». Une ligne par facture, dans l'ordre des numéros, puis les
    | totaux (formules SOUS.TOTAL : ils suivent les filtres d'Excel).
    |
    */
    public function exporter(Request $request)
    {
        $query = $this->ventesFiltrees($request)->with(['customer', 'vehicle', 'payments', 'avoir']);

        if ($request->input('statut') !== 'annulees') {
            $query->where('status', '!=', 'cancelled');
        }

        $ventes = $query->orderBy('created_at')->orderBy('id')->get();

        $date = $this->dateDePeriode($request) ?? now();
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        [$libellePeriode, $suffixe] = match ($request->input('periode')) {
            'jour'  => ['du ' . $date->format('d/m/Y'), $date->format('Y-m-d')],
            'mois'  => [$mois[$date->month - 1] . ' ' . $date->year, $date->format('Y-m')],
            'annee' => ['année ' . $date->year, (string) $date->year],
            default => ['toutes périodes', 'tout'],
        };

        $libellesModes = [
            'cash' => 'ESPECE', 'espèces' => 'ESPECE', 'especes' => 'ESPECE',
            'banque' => 'VIREMENT', 'virement' => 'VIREMENT',
            'chèque' => 'CHQ', 'cheque' => 'CHQ',
            'mobile money' => 'MOBILE MONEY', 'waafi' => 'WAAFI', 'carte' => 'CARTE',
        ];
        $modes = fn (Sale $s) => $s->payments->pluck('method')
            ->map(fn ($m) => $libellesModes[mb_strtolower(trim((string) $m))] ?? mb_strtoupper(trim((string) $m)))
            ->filter()->unique()->implode('/');

        $classeur = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $feuille = $classeur->getActiveSheet();
        $feuille->setTitle('Suivi reglement');
        $feuille->setCellValue('A1', 'Fiche de suivi de règlement des factures Magasin ' . $date->year);
        $feuille->mergeCells('A1:K1');
        $feuille->setCellValue('A2', 'Période : ' . $libellePeriode);
        $feuille->mergeCells('A2:K2');
        $feuille->fromArray(['Date', 'Numéro', 'Client', 'Type de prestation', 'PHT', 'TVA', 'TIMBRE', 'PTTC', 'Date reçu', 'Decl', 'Observation'], null, 'A4');

        $ligne = 5;

        foreach ($ventes as $s) {
            $annulee = $s->status === 'cancelled';
            $paye = round((float) $s->payments->sum('amount'));
            $total = round((float) $s->total);
            $reste = max(0, $total - $paye);
            $regle = ! $annulee && ($s->status === 'payé' || ($total > 0 && $reste <= 0));
            $dernierPaiement = $s->payments->max('created_at');

            $client = mb_strtoupper($s->customer->name ?? 'CLIENT COMPTOIR');
            if ($s->customer?->est_assurance) {
                $immat = $s->immatriculation ?: $s->vehicle?->plate_number;
                $client .= ' (ASSURANCE' . ($immat ? ' — IMMAT ' . mb_strtoupper($immat) : '') . ')';
            }

            if ($annulee) {
                $observation = $s->avoir ? 'ANNULEE PAR AVOIR ' . $s->avoir->numero : 'ANNULEE';
            } elseif ($regle) {
                $observation = $modes($s);
            } elseif ($paye > 0) {
                $observation = trim($modes($s) . ' ' . number_format($paye, 0, ',', ' ')
                    . ' — RESTE ' . number_format($reste, 0, ',', ' ') . ' (A PAYER)');
            } else {
                $observation = 'EN ATTENTE';
            }

            $feuille->fromArray([
                \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($s->created_at->copy()->startOfDay()),
                $s->invoice_number,
                $client,
                'VENTE PIECES',
                round((float) $s->subtotal - (float) $s->discount_amount),
                round((float) $s->tva),
                null,
                $total,
                $regle && $dernierPaiement ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(\Carbon\Carbon::parse($dernierPaiement)->startOfDay()) : null,
                $annulee ? 'ANNULEE' : ($regle ? 'REGLER' : 'NON REGLER'),
                $observation,
            ], null, "A{$ligne}", true);

            $ligne++;
        }

        $derniere = $ligne - 1;

        $feuille->setCellValue("D{$ligne}", 'TOTAL (' . $ventes->count() . ' factures)');
        foreach (['E', 'F', 'G', 'H'] as $col) {
            $feuille->setCellValue("{$col}{$ligne}", $ventes->isEmpty() ? 0 : "=SUBTOTAL(109,{$col}5:{$col}{$derniere})");
        }

        $remplir = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        $centre = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
        $feuille->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $feuille->getStyle('A1:K1')->getFill()->setFillType($remplir)->getStartColor()->setRGB('C4D79B');
        $feuille->getStyle('A1:K2')->getAlignment()->setHorizontal($centre);
        $feuille->getStyle('A4:K4')->getFont()->setBold(true);
        $feuille->getStyle('A4:K4')->getFill()->setFillType($remplir)->getStartColor()->setRGB('92CDDC');
        $feuille->getStyle("A4:K{$ligne}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $feuille->getStyle("A{$ligne}:K{$ligne}")->getFont()->setBold(true);
        $feuille->getStyle("A5:A{$ligne}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $feuille->getStyle("I5:I{$ligne}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $feuille->getStyle("E5:H{$ligne}")->getNumberFormat()->setFormatCode('#,##0');
        $feuille->getStyle("B5:D{$ligne}")->getAlignment()->setHorizontal($centre);
        $feuille->getStyle("J5:J{$ligne}")->getAlignment()->setHorizontal($centre);
        $feuille->getStyle("J5:J{$derniere}")->getFill()->setFillType($remplir)->getStartColor()->setRGB('D9D9D9');

        if ($ventes->isNotEmpty()) {
            $feuille->setAutoFilter("A4:K{$derniere}");
        }

        $feuille->freezePane('A5');

        foreach (range('A', 'K') as $col) {
            $feuille->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($classeur) {
            \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($classeur, 'Xlsx')->save('php://output');
        }, 'suivi-reglement-factures-magasin-' . $suffixe . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    |
    | La disponibilité provient de product_depot_stocks.
    | Un produit peut exister dans plusieurs dépôts.
    |
    */
    public function create()
    {
        $products = Product::query()
            ->with([
                'brand',
                'model',
                'depotStocks' => function ($query) {
                    $query
                        ->where('quantity', '>', 0)
                        ->orderBy('depot_id');
                },
                'depotStocks.depot',
            ])
            ->whereHas(
                'depotStocks',
                function ($query) {
                    $query->where(
                        'quantity',
                        '>',
                        0
                    );
                }
            )
            ->orderBy('designation')
            ->get();

        $customers = Customer::query()
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::query()
            ->with('customer')
            ->orderBy('plate_number')
            ->get();

        return view(
            'sales.create',
            compact(
                'products',
                'customers',
                'vehicles'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VÉHICULES ASSOCIÉS AU CLIENT
    |--------------------------------------------------------------------------
    */
    public function vehiclesByCustomer(
        Customer $customer
    ): JsonResponse {
        $vehicles = Vehicle::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->orderBy('plate_number')
            ->get([
                'id',
                'customer_id',
                'plate_number',
                'brand',
                'model',
            ])
            ->map(
                function (Vehicle $vehicle): array {
                    $description = trim(
                        implode(
                            ' ',
                            array_filter([
                                $vehicle->brand,
                                $vehicle->model,
                            ])
                        )
                    );

                    return [
                        'id' =>
                            $vehicle->id,

                        'customer_id' =>
                            $vehicle->customer_id,

                        'plate_number' =>
                            $vehicle->plate_number,

                        'label' =>
                            $description !== ''
                                ? $vehicle->plate_number
                                    . ' - '
                                    . $description
                                : $vehicle->plate_number,
                    ];
                }
            )
            ->values();

        return response()->json([
            'success' =>
                true,

            'vehicles' =>
                $vehicles,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Règles :
    | - chaque ligne choisit son dépôt ;
    | - la quantité est contrôlée dans ce dépôt précis ;
    | - sale_items.depot_id mémorise le dépôt utilisé ;
    | - products.quantity est recalculé comme somme des dépôts ;
    | - le mode de paiement n'est PAS demandé ici.
    |
    */
    public function store(
        Request $request
    ): RedirectResponse {
        // V6 : client assurance → véhicule facultatif (immatriculation libre)
        $assurance = (bool) Customer::query()
            ->whereKey($request->input('customer_id'))
            ->value('est_assurance');

        $request->validate(
            [
                'customer_id' => [
                    'required',
                    'integer',
                    'exists:customers,id',
                ],

                'vehicle_id' => [
                    Rule::requiredIf(! $assurance),
                    'nullable',
                    'integer',

                    Rule::exists(
                        'vehicles',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query->where(
                                'customer_id',
                                $request->input(
                                    'customer_id'
                                )
                            )
                    ),
                ],

                'immatriculation' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'items' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'items.*.product_id' => [
                    'required',
                    'integer',
                    'exists:products,id',
                ],

                'items.*.depot_id' => [
                    'required',
                    'integer',
                    'exists:depots,id',
                ],

                'items.*.quantity' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                'discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ],
            [
                'customer_id.required' =>
                    'Veuillez sélectionner un client.',

                'vehicle_id.required' =>
                    'Veuillez sélectionner le véhicule associé au client.',

                'vehicle_id.exists' =>
                    'Le véhicule sélectionné n’appartient pas au client choisi.',

                'items.required' =>
                    'Vous devez ajouter au moins un produit.',

                'items.*.product_id.required' =>
                    'Veuillez sélectionner un produit.',

                'items.*.product_id.exists' =>
                    'Le produit sélectionné est invalide.',

                'items.*.depot_id.required' =>
                    'Veuillez sélectionner un dépôt pour chaque produit.',

                'items.*.depot_id.exists' =>
                    'Le dépôt sélectionné est invalide.',

                'items.*.quantity.required' =>
                    'La quantité est obligatoire.',

                'items.*.quantity.numeric' =>
                    'La quantité doit être numérique.',

                'items.*.quantity.min' =>
                    'La quantité doit être supérieure à zéro.',

                'discount.max' =>
                    'La remise ne peut pas dépasser 100 %.',
            ]
        );

        DB::beginTransaction();

        try {
            $immatriculation = null;

            if ($assurance) {
                // V6 : assurance — immatriculation libre et facultative, rattachée au
            // véhicule s'il existe (même celui d'un autre client)
            $immatriculation = filled($request->input('immatriculation'))
                ? strtoupper(preg_replace('/\s+/', '', (string) $request->input('immatriculation')))
                : null;

                $vehicle = $immatriculation
                    ? Vehicle::query()->whereRaw("REPLACE(UPPER(plate_number), ' ', '') = ?", [$immatriculation])->first()
                    : null;
            } else {
                $vehicle = Vehicle::query()
                    ->where(
                        'id',
                        $request->vehicle_id
                    )
                    ->where(
                        'customer_id',
                        $request->customer_id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            /*
            |--------------------------------------------------------------------------
            | AGRÉGER LES QUANTITÉS PAR PRODUIT + DÉPÔT
            |--------------------------------------------------------------------------
            |
            | Empêche un dépassement de stock lorsque le même couple
            | produit/dépôt est présent plusieurs fois dans le formulaire.
            |
            */
            $requestedByProductDepot = [];

            foreach ($request->items as $row) {
                $productId =
                    (int) $row['product_id'];

                $depotId =
                    (int) $row['depot_id'];

                $quantity =
                    round(
                        (float) $row['quantity'],
                        2
                    );

                $key =
                    $productId
                    . ':'
                    . $depotId;

                $requestedByProductDepot[$key] =
                    ($requestedByProductDepot[$key] ?? 0)
                    + $quantity;
            }

            /*
            |--------------------------------------------------------------------------
            | VERROUILLER ET CONTRÔLER LES STOCKS
            |--------------------------------------------------------------------------
            */
            $locked = [];

            foreach (
                $requestedByProductDepot
                as $key => $requestedQty
            ) {
                [$productId, $depotId] =
                    array_map(
                        'intval',
                        explode(
                            ':',
                            $key
                        )
                    );

                $product = Product::query()
                    ->where(
                        'id',
                        $productId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                $depot = Depot::query()
                    ->where(
                        'id',
                        $depotId
                    )
                    ->firstOrFail();

                $depotStock =
                    ProductDepotStock::query()
                        ->where(
                            'product_id',
                            $productId
                        )
                        ->where(
                            'depot_id',
                            $depotId
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$depotStock) {
                    throw new \RuntimeException(
                        'Le produit '
                        . $product->reference
                        . ' n’est pas disponible dans le dépôt '
                        . $depot->name
                        . '.'
                    );
                }

                $available =
                    round(
                        (float) $depotStock->quantity,
                        2
                    );

                $requestedQty =
                    round(
                        (float) $requestedQty,
                        2
                    );

                if ($requestedQty > $available) {
                    throw new \RuntimeException(
                        'Stock insuffisant pour '
                        . $product->reference
                        . ' - '
                        . $product->designation
                        . ' dans le dépôt '
                        . $depot->name
                        . '. Disponible : '
                        . number_format(
                            $available,
                            2,
                            ',',
                            ' '
                        )
                        . '.'
                    );
                }

                $locked[$key] = [
                    'product' =>
                        $product,

                    'depot' =>
                        $depot,

                    'stock' =>
                        $depotStock,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | CALCULER LE SOUS-TOTAL
            |--------------------------------------------------------------------------
            */
            $subtotal =
                0.0;

            $validatedItems =
                [];

            // V6 : vente à une assurance → prix majorés du taux de l'assurance
            $tauxMajoration =
                Customer::findOrFail($request->customer_id)->tauxMajoration();

            foreach ($request->items as $row) {
                $productId =
                    (int) $row['product_id'];

                $depotId =
                    (int) $row['depot_id'];

                $quantity =
                    round(
                        (float) $row['quantity'],
                        2
                    );

                $key =
                    $productId
                    . ':'
                    . $depotId;

                $product =
                    $locked[$key]['product'];

                $depot =
                    $locked[$key]['depot'];

                $prixBase =
                    round(
                        (float) $product->sale_price,
                        2
                    );

                $price =
                    Customer::prixMajore(
                        $prixBase,
                        $tauxMajoration
                    );

                $lineTotal =
                    round(
                        $quantity * $price,
                        2
                    );

                $subtotal +=
                    $lineTotal;

                $validatedItems[] = [
                    'prix_base' =>
                        $prixBase,

                    'product' =>
                        $product,

                    'depot' =>
                        $depot,

                    'quantity' =>
                        $quantity,

                    'price' =>
                        $price,
                ];
            }

            $subtotal =
                round(
                    $subtotal,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | REMISE / TVA / TOTAL
            |--------------------------------------------------------------------------
            */
            $discountPercent =
                min(
                    100,
                    max(
                        0,
                        (float) (
                            $request->discount
                            ?? 0
                        )
                    )
                );

            $discountAmount =
                round(
                    (
                        $subtotal
                        *
                        $discountPercent
                    )
                    /
                    100,
                    2
                );

            $taxable =
                max(
                    0,
                    $subtotal
                    -
                    $discountAmount
                );

            $tva =
                round(
                    $taxable * 0.10,
                    2
                );

            $total =
                (int) round(
                    $taxable + $tva
                );

            /*
            |--------------------------------------------------------------------------
            | NUMÉRO DE FACTURE
            |--------------------------------------------------------------------------
            */
            $nextId =
                ((int) Sale::max('id'))
                +
                1;

            $invoiceNumber =
                'FACT-'
                . date('Y')
                . '-'
                . str_pad(
                    (string) $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | CRÉER LA VENTE
            |--------------------------------------------------------------------------
            |
            | payment_type = NULL.
            | Il sera renseigné lors du paiement.
            |
            */
            $sale = Sale::create([
                'customer_id' =>
                    $request->customer_id,

                'vehicle_id' =>
                    $vehicle?->id,

                'immatriculation' =>
                    $immatriculation,

                'user_id' =>
                    auth()->id(),

                'payment_type' =>
                    null,

                'subtotal' =>
                    $subtotal,

                'taux_majoration' =>
                    $tauxMajoration,

                'discount' =>
                    $discountPercent,

                'discount_amount' =>
                    $discountAmount,

                'tva' =>
                    $tva,

                'total' =>
                    $total,

                'status' =>
                    'vendu',

                'document_type' =>
                    'sale',

                'invoice_number' =>
                    $invoiceNumber,
            ]);

            /*
            |--------------------------------------------------------------------------
            | LIGNES + SORTIES DE STOCK
            |--------------------------------------------------------------------------
            */
            foreach ($validatedItems as $item) {
                $product =
                    $item['product'];

                $depot =
                    $item['depot'];

                $key =
                    $product->id
                    . ':'
                    . $depot->id;

                $depotStock =
                    $locked[$key]['stock'];

                SaleItem::create([
                    'sale_id' =>
                        $sale->id,

                    'product_id' =>
                        $product->id,

                    'depot_id' =>
                        $depot->id,

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],

                    'prix_base' =>
                        $item['prix_base'],
                ]);

                $depotStock->quantity =
                    max(
                        0,
                        round(
                            (float) $depotStock->quantity
                            -
                            (float) $item['quantity'],
                            2
                        )
                    );

                $depotStock->save();

                $this->syncProductQuantityFromDepots(
                    $product
                );

                StockMovement::create([
                    'product_id' =>
                        $product->id,

                    'type' =>
                        'out',

                    'quantity' =>
                        $item['quantity'],

                    'source' =>
                        'Vente | Dépôt: '
                        . $depot->name,

                    'reference' =>
                        $sale->invoice_number,

                    'user_id' =>
                        auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route(
                    'sales.invoice',
                    $sale
                )
                ->with(
                    'success',
                    'Vente enregistrée avec succès.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        return redirect()
            ->route(
                'sales.invoice',
                $sale
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ANNULATION PAR AVOIR (comme l'app Atelier)
    |--------------------------------------------------------------------------
    |
    | Une facture n'est JAMAIS supprimée. Pour l'annuler, on émet un avoir
    | (AV-AAAA-NNNN) qui la reprend en totalité : mêmes lignes, mêmes
    | montants. Dans la même transaction :
    |   - les pièces reviennent dans leur dépôt d'origine ;
    |   - la facture passe au statut « annulée » ;
    |   - le montant déjà encaissé est indiqué « à rembourser » sur l'avoir
    |     (remboursement enregistré ensuite depuis l'avoir).
    | Motif obligatoire.
    |
    */
    public function cancel(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        if ($this->isCancelled($sale)) {
            return back()->with(
                'error',
                'Cette facture est déjà annulée.'
            );
        }

        $request->validate(
            ['motif' => ['required', 'string', 'max:1000']],
            ['motif.required' => "Indiquez le motif de l'annulation."]
        );

        DB::beginTransaction();

        try {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($this->isCancelled($sale)) {
                DB::rollBack();

                return back()->with(
                    'error',
                    'Cette facture est déjà annulée.'
                );
            }

            $sale->load([
                'items.product',
                'items.depot',
                'payments',
            ]);

            $dejaPaye = round(
                (float) $sale->payments->sum('amount'),
                2
            );

            [$annee, $sequence, $numero] = Avoir::prochainNumero();

            $avoir = Avoir::create([
                'numero'               => $numero,
                'annee'                => $annee,
                'sequence'             => $sequence,
                'sale_id'              => $sale->id,
                'customer_id'          => $sale->customer_id,
                'motif'                => trim((string) $request->motif),
                'subtotal'             => $sale->subtotal ?? 0,
                'discount'             => $sale->discount ?? 0,
                'discount_amount'      => $sale->discount_amount ?? 0,
                'tva'                  => $sale->tva ?? 0,
                'total'                => $sale->total ?? 0,
                'montant_deja_paye'    => $dejaPaye,
                'montant_a_rembourser' => $dejaPaye,
                'cree_par'             => auth()->id(),
            ]);

            foreach ($sale->items as $item) {
                $avoir->lignes()->create([
                    'product_id'    => $item->product_id,
                    'depot_id'      => $item->depot_id,
                    'reference'     => $item->product->reference ?? $item->reference_libre,
                    'designation'   => $item->product->designation ?? $item->designation_libre,
                    'quantite'      => $item->quantity,
                    'prix_unitaire' => $item->price,
                    'total'         => round((float) $item->quantity * (float) $item->price, 2),
                ]);

                $this->restoreSaleItemToDepot(
                    $item,
                    $sale,
                    'Avoir ' . $numero
                );
            }

            $sale->status =
                'cancelled';

            $sale->save();

            DB::commit();

            $message = "Facture {$sale->invoice_number} annulée — avoir {$numero} émis. "
                . 'Les pièces ont été remises dans leurs dépôts d’origine.';

            if ($dejaPaye > 0) {
                $message .= ' Montant déjà encaissé : '
                    . number_format($dejaPaye, 0, ',', ' ')
                    . ' FDJ, à rembourser au client (à enregistrer sur l’avoir).';
            }

            return redirect()
                ->route(
                    'avoirs.show',
                    $avoir
                )
                ->with(
                    'success',
                    $message
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PAYMENT
    |--------------------------------------------------------------------------
    |
    | Le mode de paiement est demandé uniquement ici.
    |
    */
    public function addPayment(
        Request $request,
        Sale $sale
    ): RedirectResponse {
        if ($this->isCancelled($sale)) {
            return back()->with(
                'error',
                'Impossible de payer une facture annulée.'
            );
        }

        $request->validate(
            [
                'amount' => [
                    'required',
                    'numeric',
                    'min:1',
                ],

                'method' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ],
            [
                'amount.required' =>
                    'Veuillez saisir le montant du paiement.',

                'amount.numeric' =>
                    'Le montant du paiement doit être un nombre.',

                'amount.min' =>
                    'Le montant du paiement doit être supérieur à zéro.',

                'method.required' =>
                    'Veuillez sélectionner un mode de paiement.',
            ]
        );

        $invoiceTotal =
            (int) round(
                (float) $sale->total
            );

        $alreadyPaid =
            (int) round(
                (float) $sale->payments()
                    ->sum('amount')
            );

        $remainingAmount =
            max(
                0,
                $invoiceTotal
                -
                $alreadyPaid
            );

        if ($remainingAmount <= 0) {
            if ($sale->status !== 'payé') {
                $sale->update([
                    'status' =>
                        'payé',
                ]);
            }

            return back()->with(
                'success',
                'Cette facture est déjà entièrement payée.'
            );
        }

        $paymentAmount =
            (int) round(
                (float) $request->amount
            );

        if (
            $paymentAmount
            >
            $remainingAmount
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Le montant saisi dépasse le reste à payer de '
                    . number_format(
                        $remainingAmount,
                        0,
                        ',',
                        ' '
                    )
                    . ' FDJ.'
                );
        }

        DB::transaction(
            function () use (
                $sale,
                $paymentAmount,
                $request,
                $invoiceTotal
            ) {
                $method =
                    trim(
                        (string) $request->method
                    );

                Payment::create([
                    'sale_id' =>
                        $sale->id,

                    'amount' =>
                        $paymentAmount,

                    'method' =>
                        $method,
                ]);

                /*
                |--------------------------------------------------------------------------
                | MÉMORISER LE MODE DE PAIEMENT SUR LA FACTURE
                |--------------------------------------------------------------------------
                */
                $sale->payment_type =
                    $method;

                $paid =
                    (int) round(
                        (float) $sale->payments()
                            ->sum('amount')
                    );

                $remaining =
                    max(
                        0,
                        $invoiceTotal
                        -
                        $paid
                    );

                if ($remaining <= 0) {
                    $sale->status =
                        'payé';
                } elseif ($paid > 0) {
                    $sale->status =
                        'partiel';
                } else {
                    $sale->status =
                        'vendu';
                }

                $sale->save();
            }
        );

        $paid =
            (int) round(
                (float) $sale->payments()
                    ->sum('amount')
            );

        $remaining =
            max(
                0,
                $invoiceTotal
                -
                $paid
            );

        if ($remaining <= 0) {
            return back()->with(
                'success',
                'Paiement enregistré. La facture est entièrement payée.'
            );
        }

        return back()->with(
            'success',
            'Paiement enregistré avec succès. Reste à payer : '
            . number_format(
                $remaining,
                0,
                ',',
                ' '
            )
            . ' FDJ.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AFFICHER LA FACTURE
    |--------------------------------------------------------------------------
    */
    public function invoice(
        Sale $sale
    ) {
        $sale->load([
            'customer',
            'vehicle',
            'user',
            'items.product.brand',
            'items.product.model',
            'items.depot',
            'payments',
            'avoir',
        ]);

        $invoiceNumber =
            $sale->invoice_number
            ?:
            'FACTURE-'
            . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $totalInWords =
            $this->totalInWords(
                (float) $sale->total
            );

        return view(
            'sales.invoice',
            [
                'sale' =>
                    $sale,

                'invoiceNumber' =>
                    $invoiceNumber,

                'totalInWords' =>
                    $totalInWords,

                'isPdf' =>
                    false,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TÉLÉCHARGER LA FACTURE PDF
    |--------------------------------------------------------------------------
    */
    public function downloadInvoice(
        Sale $sale
    ) {
        $sale->load([
            'customer',
            'vehicle',
            'user',
            'items.product.brand',
            'items.product.model',
            'items.depot',
            'payments',
            'avoir',
        ]);

        $invoiceNumber =
            $sale->invoice_number
            ?:
            'FACTURE-'
            . str_pad(
                (string) $sale->id,
                6,
                '0',
                STR_PAD_LEFT
            );

        $totalInWords =
            $this->totalInWords(
                (float) $sale->total
            );

        $safeInvoiceNumber =
            preg_replace(
                '/[^A-Za-z0-9\-_]/',
                '-',
                $invoiceNumber
            );

        $pdf = Pdf::loadView(
            'sales.invoice_pdf',
            [
                'sale' =>
                    $sale,

                'invoiceNumber' =>
                    $invoiceNumber,

                'totalInWords' =>
                    $totalInWords,

                'isPdf' =>
                    true,
            ]
        )
        ->setPaper(
            'a4',
            'portrait'
        );

        return $pdf->download(
            $safeInvoiceNumber
            . '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SYNCHRONISER products.quantity
    |--------------------------------------------------------------------------
    */
    private function syncProductQuantityFromDepots(
        Product $product
    ): void {
        $total =
            round(
                (float) ProductDepotStock::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->sum('quantity'),
                2
            );

        $product->quantity =
            max(
                0,
                $total
            );

        $product->status =
            $product->quantity > 0
                ? 'disponible'
                : 'vendu';

        if (
            $product->quantity
            <=
            0
        ) {
            $product->supply_status =
                'rupture';
        } elseif (
            $product->supply_status
            ===
            'rupture'
        ) {
            $product->supply_status =
                null;
        }

        $product->save();
    }

    /*
    |--------------------------------------------------------------------------
    | RESTAURER UNE LIGNE DE VENTE DANS SON DÉPÔT
    |--------------------------------------------------------------------------
    */
    private function restoreSaleItemToDepot(
        SaleItem $item,
        Sale $sale,
        string $source
    ): void {
        $product = Product::query()
            ->where(
                'id',
                $item->product_id
            )
            ->lockForUpdate()
            ->first();

        if (!$product) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ANCIENNES VENTES SANS depot_id
        |--------------------------------------------------------------------------
        |
        | Impossible de deviner de quel dépôt elles provenaient.
        |
        */
        if (!$item->depot_id) {
            throw new \RuntimeException(
                'Impossible de restaurer automatiquement le produit '
                . (
                    $product->reference
                    ??
                    '#' . $product->id
                )
                . ' : cette ancienne ligne de vente ne contient pas de dépôt.'
            );
        }

        $depot = Depot::query()
            ->find(
                $item->depot_id
            );

        if (!$depot) {
            throw new \RuntimeException(
                'Le dépôt d’origine de la ligne de vente est introuvable.'
            );
        }

        $depotStock =
            ProductDepotStock::query()
                ->where(
                    'product_id',
                    $product->id
                )
                ->where(
                    'depot_id',
                    $depot->id
                )
                ->lockForUpdate()
                ->first();

        if (!$depotStock) {
            $depotStock =
                ProductDepotStock::create([
                    'product_id' =>
                        $product->id,

                    'depot_id' =>
                        $depot->id,

                    'quantity' =>
                        0,
                ]);
        }

        $depotStock->quantity =
            round(
                (float) $depotStock->quantity
                +
                (float) $item->quantity,
                2
            );

        $depotStock->save();

        $this->syncProductQuantityFromDepots(
            $product
        );

        StockMovement::create([
            'product_id' =>
                $product->id,

            'type' =>
                'in',

            'quantity' =>
                $item->quantity,

            'source' =>
                $source
                . ' | Dépôt: '
                . $depot->name,

            'reference' =>
                $sale->invoice_number,

            'user_id' =>
                auth()->id(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FACTURE ANNULÉE ?
    |--------------------------------------------------------------------------
    */
    private function isCancelled(
        Sale $sale
    ): bool {
        return in_array(
            strtolower(
                trim(
                    (string) $sale->status
                )
            ),
            [
                'cancelled',
                'annulé',
                'annule',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL EN LETTRES
    |--------------------------------------------------------------------------
    */
    private function totalInWords(
        float $total
    ): string {
        $numberToWords =
            new NumberToWords();

        $numberTransformer =
            $numberToWords
                ->getNumberTransformer(
                    'fr'
                );

        $totalRounded =
            (int) round(
                $total
            );

        return strtoupper(
            $numberTransformer->toWords(
                $totalRounded
            )
        )
        . ' FDJ';
    }
}
