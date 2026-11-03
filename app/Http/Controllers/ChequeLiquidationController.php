<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\ChequeLiquidation;
use App\Services\Cheques\ChequeLiquidationCalculator;
use App\Services\CompanyContextService;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class ChequeLiquidationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | EMPRESA ACTIVA
    |--------------------------------------------------------------------------
    */

    private function activeCompanyId(): int
    {
        abort_unless(auth()->check(), 401);

        $companyId = (int) app(
            CompanyContextService::class
        )->id();

        abort_if($companyId <= 0, 403);

        return $companyId;
    }

    private function ensureLiquidationCompany(
        ChequeLiquidation $liquidation
    ): int {
        $companyId = $this->activeCompanyId();

        abort_unless(
            (int) $liquidation->company_id === $companyId,
            404
        );

        return $companyId;
    }

    /*
    |--------------------------------------------------------------------------
    | LISTADO
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $this->activeCompanyId();

        $liquidations = ChequeLiquidation::query()
            ->with('cheques')
            ->orderByDesc('id')
            ->paginate(20);

        return view(
            'cheques.index',
            compact('liquidations')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NUEVA LIQUIDACIÓN
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->activeCompanyId();

        return view('cheques.create', [
            'form' => [
                'operation_date' => now()->toDateString(),
                'clearing_days' => 2,
                'annual_rate' => 62,
                'cheque_tax_rate' => 1.2,
                'gross_income_rate' => 0.6,
                'vat_rate' => 21,
                'format' => 'paper',
            ],
            'preview' => null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN DECIMAL
    |--------------------------------------------------------------------------
    */

    private function normalizeDecimal($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $value = preg_replace('/\s+/u', '', $value);

        $comma = strrpos($value, ',');
        $dot = strrpos($value, '.');

        if ($comma !== false && $dot !== false) {
            if ($comma > $dot) {
                return str_replace(
                    ',',
                    '.',
                    str_replace('.', '', $value)
                );
            }

            return str_replace(',', '', $value);
        }

        if ($comma !== false) {
            return str_replace(',', '.', $value);
        }

        if (preg_match(
            '/^\d{1,3}(?:\.\d{3})+$/',
            $value
        )) {
            return str_replace('.', '', $value);
        }

        return $value;
    }

    private function normalizeRequest(Request $request): void
    {
        $input = $request->all();

        $decimalFields = [
            'nominal_amount',
            'annual_rate',
            'cheque_tax_rate',
            'gross_income_rate',
            'vat_rate',
        ];

        foreach ($decimalFields as $field) {
            if (array_key_exists($field, $input)) {
                $input[$field] = $this->normalizeDecimal(
                    $input[$field]
                );
            }
        }

        if (
            isset($input['cheques']) &&
            is_array($input['cheques'])
        ) {
            foreach ($input['cheques'] as $index => $cheque) {
                if (!is_array($cheque)) {
                    continue;
                }

                foreach ($decimalFields as $field) {
                    if (array_key_exists($field, $cheque)) {
                        $input['cheques'][$index][$field] =
                            $this->normalizeDecimal(
                                $cheque[$field]
                            );
                    }
                }
            }
        }

        $request->merge($input);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN
    |--------------------------------------------------------------------------
    */

    private function validated(Request $request): array
    {
        $this->normalizeRequest($request);

        $multiple = $request->exists('cheques');

        $rules = [
            'client_name' => [
                'required',
                'string',
                'max:150',
            ],

            'operation_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'annual_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:300',
            ],

            'clearing_days' => [
                'required',
                'integer',
                'min:0',
                'max:30',
            ],

            'cheque_tax_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'gross_income_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'vat_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];

        if ($multiple) {
            $rules = array_merge($rules, [
                'cheques' => [
                    'required',
                    'array',
                    'min:1',
                    'max:100',
                ],

                'cheques.*.nominal_amount' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:9999999999.99',
                ],

                'cheques.*.due_date' => [
                    'required',
                    'date_format:Y-m-d',
                    'after:operation_date',
                ],

                'cheques.*.format' => [
                    'required',
                    Rule::in(['paper', 'echeq']),
                ],

                'cheques.*.bank' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'cheques.*.issuer' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'cheques.*.number' => [
                    'nullable',
                    'string',
                    'max:80',
                ],

                'cheques.*.annual_rate' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:300',
                ],

                'cheques.*.clearing_days' => [
                    'sometimes',
                    'required',
                    'integer',
                    'min:0',
                    'max:30',
                ],

                'cheques.*.cheque_tax_rate' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'cheques.*.gross_income_rate' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],

                'cheques.*.vat_rate' => [
                    'sometimes',
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ]);
        } else {
            $rules = array_merge($rules, [
                'due_date' => [
                    'required',
                    'date_format:Y-m-d',
                    'after:operation_date',
                ],

                'format' => [
                    'required',
                    Rule::in(['paper', 'echeq']),
                ],

                'bank' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'issuer' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'number' => [
                    'nullable',
                    'string',
                    'max:80',
                ],

                'nominal_amount' => [
                    'required',
                    'numeric',
                    'min:0.01',
                    'max:9999999999.99',
                ],
            ]);
        }

        $form = $request->validate($rules);

        if (!$multiple) {
            $form['cheques'] = [
                [
                    'nominal_amount' =>
                        $form['nominal_amount'],

                    'due_date' => $form['due_date'],

                    'format' => $form['format'],

                    'bank' => $form['bank'] ?? null,

                    'issuer' => $form['issuer'] ?? null,

                    'number' => $form['number'] ?? null,
                ],
            ];
        }

        return $form;
    }

    /*
    |--------------------------------------------------------------------------
    | CÁLCULO INDIVIDUAL Y CONSOLIDADO
    |--------------------------------------------------------------------------
    */

    private function calculate(
        array $form,
        ChequeLiquidationCalculator $calculator
    ): array {
        try {
            $results = $calculator->calculateMultiple(
                $form,
                $form['cheques']
            );

            $items = $results['items'];
            $totals = $results['totals'];

            if (count($items) === 1) {
                return array_merge(
                    $items[0],
                    [
                        'cheque_count' => 1,
                        'items' => $items,
                    ]
                );
            }

            /*
             * Los plazos individuales están en items.
             * Los valores de cabecera son ponderados.
             */

            $weightedCalendar = 0.0;
            $weightedClearing = 0.0;
            $totalNominal = 0.0;

            foreach ($items as $item) {
                $nominal = (float) $item['nominal_amount'];

                $totalNominal += $nominal;

                $weightedCalendar +=
                    $nominal * (int) $item['calendar_days'];

                $weightedClearing +=
                    $nominal * (int) $item['clearing_days'];
            }

            $calendarDays = $totalNominal > 0
                ? (int) round($weightedCalendar / $totalNominal)
                : 0;

            $clearingDays = $totalNominal > 0
                ? (int) round($weightedClearing / $totalNominal)
                : 0;

            $termDays = $calendarDays + $clearingDays;

            return array_merge(
                [
                    'calendar_days' => $calendarDays,
                    'clearing_days' => $clearingDays,
                    'term_days' => $termDays,

                    'annual_rate' => number_format(
                        (float) $form['annual_rate'],
                        4,
                        '.',
                        ''
                    ),

                    'cheque_tax_rate' => number_format(
                        (float) $form['cheque_tax_rate'],
                        4,
                        '.',
                        ''
                    ),

                    'gross_income_rate' => number_format(
                        (float) $form['gross_income_rate'],
                        4,
                        '.',
                        ''
                    ),

                    'vat_rate' => number_format(
                        (float) $form['vat_rate'],
                        4,
                        '.',
                        ''
                    ),
                ],
                $totals,
                [
                    'cheque_count' => count($items),
                    'items' => $items,
                ]
            );

        } catch (InvalidArgumentException $error) {
            throw ValidationException::withMessages([
                'cheques' => $error->getMessage(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SIMULACIÓN
    |--------------------------------------------------------------------------
    */

    public function preview(
        Request $request,
        ChequeLiquidationCalculator $calculator
    ): View {
        $this->activeCompanyId();

        $form = $this->validated($request);

        $preview = $this->calculate(
            $form,
            $calculator
        );

        return view(
            'cheques.create',
            compact('form', 'preview')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR LIQUIDACIÓN
    |--------------------------------------------------------------------------
    |
    | Cabecera:
    | Totales financieros consolidados.
    |
    | Detalle:
    | Cálculos financieros de cada cheque.
    |
    | Se guarda todo en una transacción SQL.
    |
    */

    public function store(
        Request $request,
        ChequeLiquidationCalculator $calculator
    ): RedirectResponse {
        $this->activeCompanyId();

        $form = $this->validated($request);

        $result = $this->calculate(
            $form,
            $calculator
        );

        /*
         * Campos financieros de cabecera.
         */

        $amountFields = [
            'calendar_days',
            'clearing_days',
            'term_days',
            'annual_rate',
            'cheque_tax_rate',
            'gross_income_rate',
            'vat_rate',
            'nominal_amount',
            'interest_amount',
            'cheque_tax_amount',
            'gross_income_amount',
            'vat_amount',
            'total_discount',
            'net_amount',
        ];

        $amounts = array_intersect_key(
            $result,
            array_flip($amountFields)
        );

        $companyId = $this->activeCompanyId();

        $liquidation = DB::transaction(function () use (
            $form,
            $result,
            $amounts,
            $companyId
        ) {
            /*
             * 1. Liquidación principal.
             */

            $liquidation = ChequeLiquidation::create(
                array_merge($amounts, [
                    'company_id' => $companyId,

                    'user_id' => auth()->id(),

                    'client_name' => trim(
                        $form['client_name']
                    ),

                    'operation_date' =>
                        $form['operation_date'],

                    'status' => 'draft',
                ])
            );

            /*
             * 2. Guardar todos los cheques.
             *
             * Cada uno conserva su propio cálculo.
             */

            foreach ($result['items'] as $item) {

                $liquidation->cheques()->create([

                    'company_id' => $companyId,

                    /*
                     * Datos identificatorios.
                     */

                    'kind' => 'third_party',

                    'format' => $item['format'],

                    'bank' => $item['bank'] ?? null,

                    'issuer' => $item['issuer'] ?? null,

                    'number' => $item['number'] ?? null,

                    'due_date' => $item['due_date'],

                    'status' => 'draft',

                    /*
                     * Nominal individual.
                     */

                    'nominal_amount' =>
                        $item['nominal_amount'],

                    /*
                     * Plazos individuales.
                     */

                    'calendar_days' =>
                        $item['calendar_days'],

                    'clearing_days' =>
                        $item['clearing_days'],

                    'term_days' =>
                        $item['term_days'],

                    /*
                     * Porcentajes particulares.
                     */

                    'annual_rate' =>
                        $item['annual_rate'],

                    'cheque_tax_rate' =>
                        $item['cheque_tax_rate'],

                    'gross_income_rate' =>
                        $item['gross_income_rate'],

                    'vat_rate' =>
                        $item['vat_rate'],

                    /*
                     * Resultados financieros.
                     */

                    'interest_amount' =>
                        $item['interest_amount'],

                    'cheque_tax_amount' =>
                        $item['cheque_tax_amount'],

                    'gross_income_amount' =>
                        $item['gross_income_amount'],

                    'vat_amount' =>
                        $item['vat_amount'],

                    'total_discount' =>
                        $item['total_discount'],

                    'net_amount' =>
                        $item['net_amount'],
                ]);
            }

            /*
             * 3. Evento único de creación.
             */

            $liquidation->events()->create([
                'user_id' => auth()->id(),

                'action' => 'created',

                'from_status' => null,

                'to_status' => 'draft',
            ]);

            return $liquidation;

        }, 3);

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                'Liquidación guardada como borrador con ' .
                count($result['items']) .
                ' cheque(s). No se registraron movimientos de fondos.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | VER LIQUIDACIÓN
    |--------------------------------------------------------------------------
    */

    public function show(
        ChequeLiquidation $liquidation
    ): View {
        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        $liquidation->load([
            'cheques',
            'events' => fn($query) =>
                $query->orderByDesc('id'),
        ]);

        $accountBalances = collect();

        if (
            $liquidation->status === 'draft' &&
            $liquidation->transaction_id === null
        ) {
            $accountBalances = AccountBalance::query()
                ->with('account')
                ->where('company_id', $companyId)
                ->where('currency', 'ARS')
                ->whereHas('account', function ($query) use (
                    $companyId
                ) {
                    $query->where(
                        'company_id',
                        $companyId
                    );
                })
                ->get()
                ->sortBy(
                    fn($balance) =>
                        $balance->account?->name ?? ''
                )
                ->values();
        }

        return view('cheques.show', [
            'liquidation' => $liquidation,
            'accountBalances' => $accountBalances,
            'banks' => \App\Support\ArgentineBanks::all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR LIQUIDACIÓN
    |--------------------------------------------------------------------------
    */

    public function confirm(
        Request $request,
        ChequeLiquidation $liquidation,
        TransactionService $transactionService
    ): RedirectResponse {
        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        $data = $request->validate([
            'account_balance_id' => [
                'required',
                'integer',
                Rule::exists(
                    'account_balances',
                    'id'
                )->where('company_id', $companyId),
            ],

            'payment_method' => [
                'required',
                Rule::in(['cash', 'transfer']),
            ],

            'destination_bank' => [
                'nullable',
                'required_if:payment_method,transfer',
                'string',
                'max:150',
            ],
        ]);

        $accountBalance = AccountBalance::withoutGlobalScopes()
            ->where('id', $data['account_balance_id'])
            ->where('company_id', $companyId)
            ->where('currency', 'ARS')
            ->first();

        if (!$accountBalance) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                    'Seleccioná una cuenta válida en pesos argentinos.',
            ]);
        }

        $account = Account::withoutGlobalScopes()
            ->whereKey($accountBalance->account_id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                    'La cuenta seleccionada no pertenece a la empresa activa.',
            ]);
        }

        $paymentMethod = $data['payment_method'];

        if (
            $paymentMethod === 'cash' &&
            $account->type !== 'cash'
        ) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                    'Para entregar efectivo seleccioná una cuenta de caja.',
            ]);
        }

        if (
            $paymentMethod === 'transfer' &&
            !in_array(
                $account->type,
                ['bank', 'wallet'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                    'Para transferir seleccioná una cuenta bancaria o billetera.',
            ]);
        }

        $destinationBank = null;

        if ($paymentMethod === 'transfer') {
            $destinationBank = trim(
                (string) (
                    $data['destination_bank'] ?? ''
                )
            );

            if ($destinationBank === '') {
                throw ValidationException::withMessages([
                    'destination_bank' =>
                        'Indicá el banco de destino de la transferencia.',
                ]);
            }
        }

        try {
            DB::transaction(function () use (
                $liquidation,
                $transactionService,
                $companyId,
                $accountBalance,
                $destinationBank
            ) {
                $locked = ChequeLiquidation::query()
                    ->where('company_id', $companyId)
                    ->whereKey($liquidation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== 'draft') {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Solo se pueden confirmar liquidaciones en borrador.',
                    ]);
                }

                if ($locked->transaction_id !== null) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'La liquidación ya tiene un movimiento asociado.',
                    ]);
                }

                if ((float) $locked->net_amount <= 0) {
                    throw ValidationException::withMessages([
                        'net_amount' =>
                            'El neto a entregar debe ser mayor que cero.',
                    ]);
                }

                /*
                 * Todos los cheques deben estar pendientes.
                 */

                $cheques = $locked->cheques()
                    ->lockForUpdate()
                    ->get();

                if ($cheques->isEmpty()) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'La liquidación no contiene cheques.',
                    ]);
                }

                if (
                    $cheques->contains(
                        fn($cheque) =>
                            $cheque->status !== 'draft'
                    )
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'Todos los cheques deben estar pendientes para confirmar la liquidación.',
                    ]);
                }

                /*
                 * Comprobación del nominal total.
                 */

                $nominalCents = (int) round(
                    (float) $locked->nominal_amount * 100
                );

                $chequesNominalCents = 0;

                foreach ($cheques as $cheque) {
                    $chequesNominalCents += (int) round(
                        (float) $cheque->nominal_amount * 100
                    );
                }

                if (
                    $nominalCents !== $chequesNominalCents
                ) {
                    throw ValidationException::withMessages([
                        'nominal_amount' =>
                            'El nominal total no coincide con los cheques de la liquidación.',
                    ]);
                }

                /*
                 * Comprobación adicional de los netos.
                 *
                 * Para liquidaciones antiguas que no tienen
                 * desglose individual, se mantiene la
                 * compatibilidad con el cálculo consolidado.
                 */

                $hasIndividualFinancialData = $cheques->every(
                    fn($cheque) =>
                        $cheque->net_amount !== null &&
                        $cheque->total_discount !== null
                );

                if ($hasIndividualFinancialData) {
                    $netCents = (int) round(
                        (float) $locked->net_amount * 100
                    );

                    $chequesNetCents = 0;

                    foreach ($cheques as $cheque) {
                        $chequesNetCents += (int) round(
                            (float) $cheque->net_amount * 100
                        );
                    }

                    if ($netCents !== $chequesNetCents) {
                        throw ValidationException::withMessages([
                            'net_amount' =>
                                'El neto total no coincide con la suma de los cheques.',
                        ]);
                    }
                }

                /*
                 * Un único egreso por el neto total.
                 */

                $transaction = $transactionService->create(
                    data: [
                        'account_balance_id' =>
                            $accountBalance->id,

                        'type' => 'expense',

                        'amount' =>
                            $locked->net_amount,

                        'description' =>
                            'Entrega de fondos por liquidación de ' .
                            $cheques->count() .
                            ' cheque(s) - Cliente: ' .
                            $locked->client_name,

                        'date' => now(),

                        'destination_bank' =>
                            $destinationBank,
                    ],

                    companyId: $companyId,

                    userId: auth()->id(),

                    source: 'cheque_liquidation',

                    externalId: (string) $locked->id
                );

                if (
                    (int) $transaction->company_id !==
                        $companyId ||

                    $transaction->source !==
                        'cheque_liquidation' ||

                    $transaction->external_id !==
                        (string) $locked->id ||

                    $transaction->type !== 'expense' ||

                    (int) $transaction->account_balance_id !==
                        (int) $accountBalance->id ||

                    round(
                        (float) $transaction->amount,
                        2
                    ) !== round(
                        (float) $locked->net_amount,
                        2
                    )
                ) {
                    throw new RuntimeException(
                        'El movimiento no coincide con la liquidación.'
                    );
                }

                /*
                 * Confirmamos la liquidación.
                 */

                $locked->update([
                    'status' => 'confirmed',

                    'transaction_id' =>
                        $transaction->id,

                    'confirmed_at' => now(),
                ]);

                /*
                 * Todos los cheques ingresan en cartera.
                 */

                foreach ($cheques as $cheque) {
                    $cheque->update([
                        'status' => 'in_portfolio',
                    ]);
                }

                $locked->events()->create([
                    'user_id' => auth()->id(),

                    'action' => 'confirmed',

                    'from_status' => 'draft',

                    'to_status' => 'confirmed',
                ]);

            }, 3);

        } catch (RuntimeException $error) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                    $error->getMessage(),
            ]);
        }

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                'Operación confirmada. Se registró un único egreso por el neto total y todos los cheques ingresaron en cartera.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RETENER
    |--------------------------------------------------------------------------
    */

    public function hold(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        return $this->changeStatus(
            $liquidation,
            'draft',
            'held'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LIBERAR
    |--------------------------------------------------------------------------
    */

    public function release(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        return $this->changeStatus(
            $liquidation,
            'held',
            'draft'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE ESTADO
    |--------------------------------------------------------------------------
    */

    private function changeStatus(
        ChequeLiquidation $liquidation,
        string $from,
        string $to
    ): RedirectResponse {
        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        DB::transaction(function () use (
            $liquidation,
            $companyId,
            $from,
            $to
        ) {
            $locked = ChequeLiquidation::query()
                ->where('company_id', $companyId)
                ->whereKey($liquidation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== $from) {
                throw ValidationException::withMessages([
                    'status' =>
                        'La liquidación cambió de estado. Actualizá la pantalla.',
                ]);
            }

            if ($locked->transaction_id !== null) {
                throw ValidationException::withMessages([
                    'status' =>
                        'No se puede cambiar el estado de una operación con movimientos financieros.',
                ]);
            }

            $locked->update([
                'status' => $to,
            ]);

            $locked->events()->create([
                'user_id' => auth()->id(),

                'action' => $to === 'held'
                    ? 'held'
                    : 'released',

                'from_status' => $from,

                'to_status' => $to,
            ]);
        });

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                $to === 'held'
                    ? 'Liquidación retenida.'
                    : 'Liquidación liberada como borrador.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        DB::transaction(function () use (
            $liquidation,
            $companyId
        ) {
            $locked = ChequeLiquidation::query()
                ->where('company_id', $companyId)
                ->whereKey($liquidation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                !in_array(
                    $locked->status,
                    ['draft', 'held'],
                    true
                ) ||
                $locked->transaction_id !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'No se puede eliminar una liquidación con ejecución financiera.',
                ]);
            }

            $locked->events()->delete();

            $locked->cheques()->delete();

            $locked->delete();
        });

        return redirect()
            ->route('cheques.index')
            ->with(
                'success',
                'La operación fue eliminada correctamente.'
            );
    }
}
