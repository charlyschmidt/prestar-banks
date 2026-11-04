<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\Cheque;
use App\Models\ChequeLiquidation;
use App\Models\ChequeLiquidationDelivery;
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
use App\Models\AccountDailyBalance;
use App\Services\FinancialDayService;
use App\Events\ChequeLiquidationConfirmed;

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



    public function saveDeliveries(
        Request $request,
        ChequeLiquidation $liquidation
    ): RedirectResponse {

        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        /*
    |--------------------------------------------------------------------------
    | 1. Validar estructura de las entregas
    |--------------------------------------------------------------------------
    */

        $data = $request->validate([
            'deliveries' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'deliveries.*.type' => [
                'required',
                Rule::in([
                    'cash',
                    'transfer',
                    'third_party_cheque',
                ]),
            ],
            'deliveries.*.account_balance_id' => [
                'nullable',
                'integer',
            ],
            'deliveries.*.delivered_cheque_id' => [
                'nullable',
                'integer',
            ],
            'deliveries.*.currency' => [
                'required',
                Rule::in(['ARS', 'USD', 'EUR']),
            ],
            'deliveries.*.amount' => [
                'required',
                'string',
                'max:40',
            ],
            'deliveries.*.exchange_rate' => [
                'required',
                'string',
                'max:40',
            ],
            'deliveries.*.destination_bank' => [
                'nullable',
                'string',
                'max:150',
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | 2. Normalización de importes
    |--------------------------------------------------------------------------
    |
    | Se aceptan:
    | 1500000
    | 1500000.50
    | 1.500.000,50
    |
    */

        $parseDecimal = static function (
            mixed $value,
            int $decimals
        ): string {

            $value = trim((string) $value);
            $value = str_replace(' ', '', $value);

            if (str_contains($value, ',')) {

                if (!preg_match(
                    '/^\d{1,3}(\.\d{3})*(,\d+)?$|^\d+(,\d+)?$/',
                    $value
                )) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        'El formato de un importe es inválido.',
                    ]);
                }

                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }

            if (!preg_match(
                '/^\d+(\.\d+)?$/',
                $value
            )) {
                throw ValidationException::withMessages([
                    'deliveries' =>
                    'El formato de un importe es inválido.',
                ]);
            }

            $parts = explode('.', $value);

            if (
                isset($parts[1]) &&
                strlen($parts[1]) > $decimals
            ) {
                throw ValidationException::withMessages([
                    'deliveries' =>
                    'Un importe tiene demasiados decimales.',
                ]);
            }

            $number = (float) $value;

            if (
                !is_finite($number) ||
                $number <= 0 ||
                $number > 1000000000
            ) {
                throw ValidationException::withMessages([
                    'deliveries' =>
                    'El importe o cotización está fuera del rango permitido.',
                ]);
            }

            return number_format(
                $number,
                $decimals,
                '.',
                ''
            );
        };

        /*
    |--------------------------------------------------------------------------
    | 3. Transacción de base de datos
    |--------------------------------------------------------------------------
    */

        DB::transaction(function () use (
            $liquidation,
            $companyId,
            $data,
            $parseDecimal
        ) {

            /*
        |--------------------------------------------------------------------------
        | Bloquear liquidación
        |--------------------------------------------------------------------------
        */

            $locked = ChequeLiquidation::query()
                ->where('company_id', $companyId)
                ->whereKey($liquidation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $locked->status !== 'draft' ||
                $locked->transaction_id !== null
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                    'La liquidación ya no permite modificar sus pagos.',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Verificar entregas anteriores
        |--------------------------------------------------------------------------
        */

            $previousDeliveries = $locked->deliveries()
                ->lockForUpdate()
                ->get();

            if ($previousDeliveries->contains(
                fn($delivery) =>
                $delivery->transaction_id !== null
            )) {
                throw ValidationException::withMessages([
                    'deliveries' =>
                    'No se pueden modificar entregas que ya generaron movimientos.',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 4. Jornada financiera actual
        |--------------------------------------------------------------------------
        */

            $financialDay = app(
                \App\Services\FinancialDayService::class
            )->current($companyId);

            /*
        |--------------------------------------------------------------------------
        | 5. Preparar acumuladores
        |--------------------------------------------------------------------------
        */

            $rows = [];

            $totalCents = 0;

            $selectedChequeIds = [];

            /*
         * Total a debitar de cada AccountBalance.
         *
         * Incluye impuestos de transferencia.
         */
            $debitsByBalance = [];

            /*
         * Información para mostrar errores claros.
         */
            $balanceDetails = [];

            /*
        |--------------------------------------------------------------------------
        | 6. Procesar y validar cada entrega
        |--------------------------------------------------------------------------
        */

            foreach ($data['deliveries'] as $index => $item) {

                $number = $index + 1;

                $type = $item['type'];

                $currency = $item['currency'];

                $amount = $parseDecimal(
                    $item['amount'],
                    2
                );

                $rate = $parseDecimal(
                    $item['exchange_rate'],
                    6
                );

                $amountCents = (int) round(
                    (float) $amount * 100
                );

                $rateValue = (float) $rate;

                /*
            |--------------------------------------------------------------------------
            | Cotización
            |--------------------------------------------------------------------------
            */

                if (
                    $currency === 'ARS' &&
                    $rateValue !== 1.0
                ) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        "Entrega #{$number}: la cotización de ARS debe ser 1.",
                    ]);
                }

                $equivalent = $amountCents * $rateValue;

                if (
                    !is_finite($equivalent) ||
                    $equivalent <= 0 ||
                    $equivalent > 100000000000
                ) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        "Entrega #{$number}: el equivalente en ARS es inválido.",
                    ]);
                }

                $equivalentCents = (int) round(
                    $equivalent
                );

                if ($equivalentCents <= 0) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        "Entrega #{$number}: el equivalente debe ser positivo.",
                    ]);
                }

                $accountId = null;
                $chequeId = null;
                $notes = null;

                /*
            |--------------------------------------------------------------------------
            | 7. Efectivo o transferencia
            |--------------------------------------------------------------------------
            */

                if (in_array(
                    $type,
                    ['cash', 'transfer'],
                    true
                )) {

                    if (!$financialDay) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'No hay una jornada financiera abierta. Primero iniciá el día.',
                        ]);
                    }

                    if (
                        empty($item['account_balance_id']) ||
                        !empty($item['delivered_cheque_id'])
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: seleccioná una cuenta válida.",
                        ]);
                    }

                    /*
                 * Validar AccountBalance.
                 */

                    $balance = AccountBalance::withoutGlobalScopes()
                        ->with('account')
                        ->where('company_id', $companyId)
                        ->whereKey(
                            $item['account_balance_id']
                        )
                        ->first();

                    if (
                        !$balance ||
                        !$balance->account ||
                        (int) $balance->account->company_id !==
                        $companyId
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: la cuenta no pertenece a la empresa activa.",
                        ]);
                    }

                    if ($balance->currency !== $currency) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: la moneda no coincide con la cuenta.",
                        ]);
                    }

                    $account = $balance->account;

                    /*
                 * Validar tipo de cuenta.
                 */

                    if (
                        $type === 'cash' &&
                        $account->type !== 'cash'
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: el efectivo debe salir de una caja.",
                        ]);
                    }

                    if (
                        $type === 'transfer' &&
                        !in_array(
                            $account->type,
                            ['bank', 'wallet'],
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: la transferencia debe salir de un banco o billetera.",
                        ]);
                    }

                    $accountId = $account->id;

                    /*
                |--------------------------------------------------------------------------
                | Banco de destino
                |--------------------------------------------------------------------------
                */

                    if ($type === 'transfer') {

                        $notes = trim(
                            (string) (
                                $item['destination_bank'] ?? ''
                            )
                        );

                        if ($notes === '') {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                "Entrega #{$number}: seleccioná el banco de destino.",
                            ]);
                        }

                        $validBanks = array_merge(
                            ['No corresponde'],
                            \App\Support\ArgentineBanks::all()
                        );

                        if (!in_array(
                            $notes,
                            $validBanks,
                            true
                        )) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                "Entrega #{$number}: el banco de destino es inválido.",
                            ]);
                        }
                    }

                    /*
                |--------------------------------------------------------------------------
                | Calcular impuesto de transferencia
                |--------------------------------------------------------------------------
                |
                | Misma regla actual de TransactionService:
                | un destino no vacío genera el impuesto
                | configurado para la cuenta.
                |
                | IMPORTANTE: "No corresponde" también
                | es un valor no vacío.
                |
                */

                    $taxRate = 0.0;

                    if ($type === 'transfer') {

                        $taxRate = (float) (
                            $account->transfer_tax_rate ?? 0
                        );
                    }

                    if (
                        !is_finite($taxRate) ||
                        $taxRate < 0
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: el impuesto de transferencia es inválido.",
                        ]);
                    }

                    $taxCents = (int) round(
                        $amountCents * $taxRate / 100
                    );

                    $debitCents =
                        $amountCents + $taxCents;

                    /*
                |--------------------------------------------------------------------------
                | Acumular débitos de una misma cuenta
                |--------------------------------------------------------------------------
                |
                | Utilizamos AccountBalance ID como clave,
                | porque identifica cuenta + moneda.
                |
                */

                    $balanceId = (int) $balance->id;

                    $debitsByBalance[$balanceId] =
                        ($debitsByBalance[$balanceId] ?? 0)
                        + $debitCents;

                    $balanceDetails[$balanceId] = [
                        'name' => $account->name,
                        'currency' => $balance->currency,
                        'account_id' => $account->id,
                    ];
                }

                /*
            |--------------------------------------------------------------------------
            | 8. Cheques de terceros
            |--------------------------------------------------------------------------
            */

                if ($type === 'third_party_cheque') {

                    if (
                        empty($item['delivered_cheque_id']) ||
                        !empty($item['account_balance_id']) ||
                        $currency !== 'ARS' ||
                        $rateValue !== 1.0
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: los datos del cheque son inválidos.",
                        ]);
                    }

                    $chequeId = (int) (
                        $item['delivered_cheque_id']
                    );

                    if (in_array(
                        $chequeId,
                        $selectedChequeIds,
                        true
                    )) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'No podés utilizar el mismo cheque dos veces.',
                        ]);
                    }

                    $selectedChequeIds[] = $chequeId;

                    $cheque = Cheque::query()
                        ->where('company_id', $companyId)
                        ->whereKey($chequeId)
                        ->lockForUpdate()
                        ->first();

                    if (
                        !$cheque ||
                        !$cheque->canBeDelivered() ||
                        (int) $cheque->cheque_liquidation_id ===
                        (int) $locked->id
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: el cheque no está disponible en cartera.",
                        ]);
                    }

                    $nominalCents = (int) round(
                        (float) $cheque->nominal_amount * 100
                    );

                    if ($nominalCents !== $amountCents) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: el importe debe coincidir con el nominal del cheque.",
                        ]);
                    }

                    /*
                 * Comprobar reservas de otras
                 * liquidaciones.
                 */

                    $usedElsewhere =
                        ChequeLiquidationDelivery::query()
                        ->where(
                            'delivered_cheque_id',
                            $chequeId
                        )
                        ->where(
                            'cheque_liquidation_id',
                            '!=',
                            $locked->id
                        )
                        ->exists();

                    if ($usedElsewhere) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            "Entrega #{$number}: el cheque está asignado a otra liquidación.",
                        ]);
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | 9. Preparar registro
            |--------------------------------------------------------------------------
            */

                $rows[] = [
                    'type' => $type,
                    'currency' => $currency,
                    'amount' => $amount,
                    'exchange_rate' => $rate,

                    'amount_ars' => number_format(
                        $equivalentCents / 100,
                        2,
                        '.',
                        ''
                    ),

                    'account_id' => $accountId,

                    'delivered_cheque_id' => $chequeId,

                    'transaction_id' => null,

                    'notes' => $notes,
                ];

                $totalCents += $equivalentCents;
            }

            /*
        |--------------------------------------------------------------------------
        | 10. Verificar distribución contra el neto
        |--------------------------------------------------------------------------
        */

            $netCents = (int) round(
                (float) $locked->net_amount * 100
            );

            if ($netCents <= 0) {
                throw ValidationException::withMessages([
                    'net_amount' =>
                    'El neto de la liquidación debe ser mayor a cero.',
                ]);
            }

            if ($totalCents > $netCents) {
                throw ValidationException::withMessages([
                    'deliveries' =>
                    'La distribución supera el neto de la liquidación.',
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 11. Verificar saldos reales de la jornada
        |--------------------------------------------------------------------------
        |
        | Se bloquean los saldos en orden ascendente.
        |
        | Esto ayuda a evitar conflictos cuando
        | varias operaciones usan las mismas cuentas.
        |
        | IMPORTANTE:
        | Esta validación no reserva el dinero.
        | TransactionService verificará nuevamente
        | al confirmar la operación.
        |
        */

            ksort($debitsByBalance, SORT_NUMERIC);

            foreach ($debitsByBalance as $balanceId => $debitCents) {

                $dailyBalance =
                    \App\Models\AccountDailyBalance::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where(
                        'financial_day_id',
                        $financialDay->id
                    )
                    ->where(
                        'account_balance_id',
                        $balanceId
                    )
                    ->lockForUpdate()
                    ->first();

                if (
                    !$dailyBalance ||
                    (int) $dailyBalance->account_id !==
                    (int) $balanceDetails[$balanceId]['account_id']
                ) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        'Una cuenta no tiene saldo registrado en la jornada actual.',
                    ]);
                }

                $availableCents = (int) round(
                    (float) $dailyBalance->current_balance * 100
                );

                if ($debitCents > $availableCents) {

                    $details = $balanceDetails[$balanceId];

                    $available = number_format(
                        $availableCents / 100,
                        2,
                        ',',
                        '.'
                    );

                    $required = number_format(
                        $debitCents / 100,
                        2,
                        ',',
                        '.'
                    );

                    throw ValidationException::withMessages([
                        'deliveries' =>
                        "Saldo insuficiente en {$details['name']} ({$details['currency']}). " .
                            "Disponible: {$available}. " .
                            "Necesario, incluyendo impuestos: {$required}.",
                    ]);
                }
            }

            /*
        |--------------------------------------------------------------------------
        | 12. Reemplazar distribución
        |--------------------------------------------------------------------------
        |
        | Todavía NO se generan transacciones.
        |
        | Los cheques permanecen en cartera,
        | aunque quedan asignados a esta operación
        | mediante delivered_cheque_id.
        |
        */

            $locked->deliveries()->delete();

            foreach ($rows as $row) {
                $locked->deliveries()->create($row);
            }
        }, 3);

        /*
    |--------------------------------------------------------------------------
    | 13. Volver a la liquidación
    |--------------------------------------------------------------------------
    */

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                'Los medios de pago se guardaron correctamente. Todavía no se descontaron fondos ni se entregaron cheques.'
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

        /*
    |--------------------------------------------------------------------------
    | 1. Empresa activa
    |--------------------------------------------------------------------------
    */

        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        /*
    |--------------------------------------------------------------------------
    | 2. Relaciones de la liquidación
    |--------------------------------------------------------------------------
    */

        $liquidation->load([
            'cheques',

            'deliveries' => fn($query) =>
            $query->orderBy('id'),

            'deliveries.account',

            'deliveries.deliveredCheque',

            'deliveries.transaction',

            'events' => fn($query) =>
            $query->orderByDesc('id'),
        ]);

        /*
    |--------------------------------------------------------------------------
    | 3. Inicializar datos
    |--------------------------------------------------------------------------
    */

        $accountBalances = collect();

        $portfolioCheques = collect();

        $hasOpenFinancialDay = false;

        /*
    |--------------------------------------------------------------------------
    | 4. Medios disponibles
    |--------------------------------------------------------------------------
    */

        if (
            $liquidation->status === 'draft' &&
            $liquidation->transaction_id === null
        ) {

            /*
        |--------------------------------------------------------------------------
        | 5. Jornada financiera actual
        |--------------------------------------------------------------------------
        |
        | Usamos el mismo servicio que TransactionService.
        |
        | Nunca tomamos el saldo inicial como disponible.
        | El disponible real está en AccountDailyBalance.
        |
        */

            $financialDay = app(
                FinancialDayService::class
            )->current($companyId);

            $hasOpenFinancialDay = $financialDay !== null;

            /*
        |--------------------------------------------------------------------------
        | 6. Cuentas de la empresa
        |--------------------------------------------------------------------------
        |
        | Cargamos ARS, USD y EUR.
        |
        */

            $accountBalances = AccountBalance::query()
                ->with('account')
                ->where('company_id', $companyId)
                ->whereIn(
                    'currency',
                    ['ARS', 'USD', 'EUR']
                )
                ->whereHas('account', function ($query) use (
                    $companyId
                ) {
                    $query->where(
                        'company_id',
                        $companyId
                    );
                })
                ->get()
                ->sortBy([
                    [
                        fn($balance) =>
                        $balance->account?->name ?? '',
                        'asc',
                    ],
                    [
                        'currency',
                        'asc',
                    ],
                ])
                ->values();

            /*
        |--------------------------------------------------------------------------
        | 7. Saldos reales de la jornada
        |--------------------------------------------------------------------------
        |
        | Buscamos todos los saldos en una consulta
        | para evitar hacer una consulta por cuenta.
        |
        */

            $dailyBalances = collect();

            if ($financialDay) {

                $dailyBalances = AccountDailyBalance::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where(
                        'financial_day_id',
                        $financialDay->id
                    )
                    ->whereIn(
                        'account_balance_id',
                        $accountBalances->pluck('id')->all()
                    )
                    ->get()
                    ->keyBy('account_balance_id');
            }

            /*
        |--------------------------------------------------------------------------
        | 8. Incorporar saldo y tasa de transferencia
        |--------------------------------------------------------------------------
        |
        | available_balance:
        |   Saldo real disponible en la moneda.
        |
        | transfer_tax_rate:
        |   Impuesto configurado para la cuenta.
        |
        | Si no existe saldo de jornada, usamos NULL,
        | nunca un saldo histórico.
        |
        */

            $accountBalances->each(
                function ($balance) use ($dailyBalances) {

                    $daily = $dailyBalances->get(
                        $balance->id
                    );

                    $balance->setAttribute(
                        'available_balance',
                        $daily
                            ? number_format(
                                (float) $daily->current_balance,
                                2,
                                '.',
                                ''
                            )
                            : null
                    );

                    $balance->setAttribute(
                        'transfer_tax_rate',
                        number_format(
                            (float) (
                                $balance->account?->transfer_tax_rate
                                ?? 0
                            ),
                            4,
                            '.',
                            ''
                        )
                    );
                }
            );

            /*
        |--------------------------------------------------------------------------
        | 9. Cheques de terceros en cartera
        |--------------------------------------------------------------------------
        */

            $portfolioCheques = Cheque::query()
                ->where('company_id', $companyId)
                ->where(
                    'status',
                    Cheque::STATUS_IN_PORTFOLIO
                )
                ->where(
                    'cheque_liquidation_id',
                    '!=',
                    $liquidation->id
                )
                ->where('nominal_amount', '>', 0)
                ->orderBy('due_date')
                ->orderBy('bank')
                ->orderBy('number')
                ->get();
        }

        /*
    |--------------------------------------------------------------------------
    | 10. Vista
    |--------------------------------------------------------------------------
    */

        return view('cheques.show', [
            'liquidation' => $liquidation,

            'accountBalances' => $accountBalances,

            'portfolioCheques' => $portfolioCheques,

            'hasOpenFinancialDay' => $hasOpenFinancialDay,

            'banks' => \App\Support\ArgentineBanks::all(),
        ]);
    }



    public function confirm(
        Request $request,
        ChequeLiquidation $liquidation,
        TransactionService $transactionService
    ): RedirectResponse {

        $companyId = $this->ensureLiquidationCompany(
            $liquidation
        );

        try {

            DB::transaction(function () use (
                $liquidation,
                $transactionService,
                $companyId
            ) {

                /*
            |--------------------------------------------------------------------------
            | 1. Bloquear liquidación
            |--------------------------------------------------------------------------
            */

                $locked = ChequeLiquidation::query()
                    ->where('company_id', $companyId)
                    ->whereKey($liquidation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== 'draft') {
                    throw ValidationException::withMessages([
                        'status' =>
                        'La liquidación ya no está pendiente de confirmación.',
                    ]);
                }

                if ($locked->transaction_id !== null) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'Esta liquidación ya tiene un movimiento financiero.',
                    ]);
                }

                $netCents = (int) round(
                    (float) $locked->net_amount * 100
                );

                if ($netCents <= 0) {
                    throw ValidationException::withMessages([
                        'net_amount' =>
                        'El neto de la liquidación debe ser positivo.',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 2. Validar cheques recibidos
            |--------------------------------------------------------------------------
            */

                $cheques = $locked->cheques()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($cheques->isEmpty()) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'La liquidación no contiene cheques.',
                    ]);
                }

                if ($cheques->contains(
                    fn($cheque) => $cheque->status !== 'draft'
                )) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'Todos los cheques recibidos deben estar pendientes.',
                    ]);
                }

                $nominalCents = (int) round(
                    (float) $locked->nominal_amount * 100
                );

                $chequesNominalCents = $cheques->sum(
                    fn($cheque) =>
                    (int) round(
                        (float) $cheque->nominal_amount * 100
                    )
                );

                if ($nominalCents !== $chequesNominalCents) {
                    throw ValidationException::withMessages([
                        'nominal_amount' =>
                        'El nominal total no coincide con los cheques recibidos.',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 3. Validar netos individuales
            |--------------------------------------------------------------------------
            |
            | Mantener compatibilidad con liquidaciones
            | anteriores sin desglose individual.
            |
            */

                $hasIndividualData = $cheques->every(
                    fn($cheque) =>
                    $cheque->net_amount !== null &&
                        $cheque->total_discount !== null
                );

                if ($hasIndividualData) {

                    $chequesNetCents = $cheques->sum(
                        fn($cheque) =>
                        (int) round(
                            (float) $cheque->net_amount * 100
                        )
                    );

                    if ($chequesNetCents !== $netCents) {
                        throw ValidationException::withMessages([
                            'net_amount' =>
                            'El neto no coincide con la suma individual de los cheques.',
                        ]);
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | 4. Bloquear entregas guardadas
            |--------------------------------------------------------------------------
            */

                $deliveries = $locked->deliveries()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($deliveries->isEmpty()) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        'Primero guardá los medios de pago.',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 5. Validar total distribuido
            |--------------------------------------------------------------------------
            */

                $distributedCents = 0;

                foreach ($deliveries as $delivery) {

                    if ($delivery->transaction_id !== null) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'Una entrega ya fue procesada.',
                        ]);
                    }

                    if (!in_array(
                        $delivery->type,
                        [
                            'cash',
                            'transfer',
                            'third_party_cheque',
                        ],
                        true
                    )) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'Se encontró un medio de pago inválido.',
                        ]);
                    }

                    if (!in_array(
                        $delivery->currency,
                        ['ARS', 'USD', 'EUR'],
                        true
                    )) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'La moneda de una entrega es inválida.',
                        ]);
                    }

                    $amountCents = (int) round(
                        (float) $delivery->amount * 100
                    );

                    $rate = (float) $delivery->exchange_rate;

                    $equivalentCents = (int) round(
                        (float) $delivery->amount_ars * 100
                    );

                    if (
                        $amountCents <= 0 ||
                        !is_finite($rate) ||
                        $rate <= 0 ||
                        $rate > 1000000000 ||
                        $equivalentCents <= 0
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'Una entrega tiene importes inválidos.',
                        ]);
                    }

                    if (
                        $delivery->currency === 'ARS' &&
                        $rate !== 1.0
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'La cotización de ARS debe ser 1.',
                        ]);
                    }

                    $expectedCents = (int) round(
                        $amountCents * $rate
                    );

                    if ($expectedCents !== $equivalentCents) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'El equivalente en pesos de una entrega no coincide.',
                        ]);
                    }

                    $distributedCents += $equivalentCents;
                }

                if ($distributedCents !== $netCents) {
                    throw ValidationException::withMessages([
                        'deliveries' =>
                        'La distribución debe coincidir exactamente con el neto a entregar.',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 6. Validar los medios antes de ejecutar
            |--------------------------------------------------------------------------
            |
            | Primero verificamos la disponibilidad.
            | Después procesamos los movimientos.
            |
            */

                $monetaryDeliveries = [];

                $portfolioCheques = [];

                $selectedChequeIds = [];

                $debitsByBalance = [];

                $balanceDetails = [];

                foreach ($deliveries as $delivery) {

                    /*
                |--------------------------------------------------------------------------
                | A. Cheque de terceros
                |--------------------------------------------------------------------------
                */

                    if ($delivery->isThirdPartyCheque()) {

                        if (
                            $delivery->account_id !== null ||
                            $delivery->delivered_cheque_id === null ||
                            $delivery->currency !== 'ARS'
                        ) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'Los datos del cheque entregado son inválidos.',
                            ]);
                        }

                        $chequeId = (int) $delivery->delivered_cheque_id;

                        if (in_array(
                            $chequeId,
                            $selectedChequeIds,
                            true
                        )) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'No podés entregar el mismo cheque dos veces.',
                            ]);
                        }

                        $selectedChequeIds[] = $chequeId;

                        $portfolioCheque = Cheque::query()
                            ->where('company_id', $companyId)
                            ->whereKey($chequeId)
                            ->lockForUpdate()
                            ->first();

                        if (
                            !$portfolioCheque ||
                            !$portfolioCheque->canBeDelivered() ||
                            (int) $portfolioCheque->cheque_liquidation_id ===
                            (int) $locked->id
                        ) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'Un cheque ya no está disponible en cartera.',
                            ]);
                        }

                        $chequeCents = (int) round(
                            (float) $portfolioCheque->nominal_amount * 100
                        );

                        $deliveryCents = (int) round(
                            (float) $delivery->amount * 100
                        );

                        if ($chequeCents !== $deliveryCents) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'El importe del cheque no coincide con su nominal.',
                            ]);
                        }

                        /*
                     * Verificar que no esté asignado
                     * a otra operación.
                     */

                        $usedElsewhere = ChequeLiquidationDelivery::query()
                            ->where('delivered_cheque_id', $chequeId)
                            ->where(
                                'cheque_liquidation_id',
                                '!=',
                                $locked->id
                            )
                            ->exists();

                        if ($usedElsewhere) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'Un cheque está asignado a otra liquidación.',
                            ]);
                        }

                        $portfolioCheques[$chequeId] = $portfolioCheque;

                        continue;
                    }

                    /*
                |--------------------------------------------------------------------------
                | B. Efectivo y transferencias
                |--------------------------------------------------------------------------
                */

                    if (
                        $delivery->account_id === null ||
                        $delivery->delivered_cheque_id !== null
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'Una entrega monetaria no tiene una cuenta válida.',
                        ]);
                    }

                    $account = Account::withoutGlobalScopes()
                        ->where('company_id', $companyId)
                        ->whereKey($delivery->account_id)
                        ->first();

                    if (!$account) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'Una cuenta no pertenece a la empresa activa.',
                        ]);
                    }

                    if (
                        $delivery->isCash() &&
                        $account->type !== 'cash'
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'El efectivo debe salir de una caja.',
                        ]);
                    }

                    if (
                        $delivery->isTransfer() &&
                        !in_array(
                            $account->type,
                            ['bank', 'wallet'],
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'La transferencia debe salir de un banco o billetera.',
                        ]);
                    }

                    $accountBalance = AccountBalance::withoutGlobalScopes()
                        ->where('company_id', $companyId)
                        ->where('account_id', $account->id)
                        ->where('currency', $delivery->currency)
                        ->first();

                    if (!$accountBalance) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'La cuenta no dispone de la moneda seleccionada.',
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Banco de destino e impuesto
                |--------------------------------------------------------------------------
                */

                    $destinationBank = null;
                    $transferTaxCents = 0;

                    if ($delivery->isTransfer()) {

                        $destinationBank = trim(
                            (string) $delivery->notes
                        );

                        $validBanks = array_merge(
                            ['No corresponde'],
                            \App\Support\ArgentineBanks::all()
                        );

                        if (
                            $destinationBank === '' ||
                            !in_array(
                                $destinationBank,
                                $validBanks,
                                true
                            )
                        ) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'El banco de destino no es válido.',
                            ]);
                        }

                        /*
                     * Misma regla de TransactionService:
                     * todo destino no vacío aplica la
                     * tasa configurada en la cuenta.
                     */

                        $taxRate = (float) (
                            $account->transfer_tax_rate ?? 0
                        );

                        if (
                            !is_finite($taxRate) ||
                            $taxRate < 0
                        ) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'La tasa de transferencia es inválida.',
                            ]);
                        }

                        $transferTaxCents = (int) round(
                            (float) $delivery->amount *
                                $taxRate
                        );
                    }

                    $amountCents = (int) round(
                        (float) $delivery->amount * 100
                    );

                    $debitCents = $amountCents + $transferTaxCents;

                    $balanceId = (int) $accountBalance->id;

                    $debitsByBalance[$balanceId] =
                        ($debitsByBalance[$balanceId] ?? 0)
                        + $debitCents;

                    $balanceDetails[$balanceId] = [
                        'account_id' => $account->id,
                        'name' => $account->name,
                        'currency' => $accountBalance->currency,
                    ];

                    $monetaryDeliveries[] = [
                        'delivery' => $delivery,
                        'account_balance' => $accountBalance,
                        'destination_bank' => $destinationBank,
                    ];
                }

                /*
            |--------------------------------------------------------------------------
            | 7. Validar saldos de la jornada
            |--------------------------------------------------------------------------
            */

                if (count($monetaryDeliveries) > 0) {

                    $financialDay = app(
                        \App\Services\FinancialDayService::class
                    )->current($companyId);

                    if (!$financialDay) {
                        throw ValidationException::withMessages([
                            'deliveries' =>
                            'No hay una jornada financiera abierta.',
                        ]);
                    }

                    ksort($debitsByBalance, SORT_NUMERIC);

                    foreach ($debitsByBalance as $balanceId => $requiredCents) {

                        $dailyBalance =
                            \App\Models\AccountDailyBalance::withoutGlobalScopes()
                            ->where('company_id', $companyId)
                            ->where(
                                'financial_day_id',
                                $financialDay->id
                            )
                            ->where(
                                'account_balance_id',
                                $balanceId
                            )
                            ->lockForUpdate()
                            ->first();

                        if (
                            !$dailyBalance ||
                            (int) $dailyBalance->account_id !==
                            (int) $balanceDetails[$balanceId]['account_id']
                        ) {
                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'Una cuenta no tiene saldo en la jornada actual.',
                            ]);
                        }

                        $availableCents = (int) round(
                            (float) $dailyBalance->current_balance * 100
                        );

                        if ($requiredCents > $availableCents) {

                            $details = $balanceDetails[$balanceId];

                            throw ValidationException::withMessages([
                                'deliveries' =>
                                'Saldo insuficiente en ' .
                                    $details['name'] .
                                    ' (' . $details['currency'] . ').',
                            ]);
                        }
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | 8. Generar egresos monetarios
            |--------------------------------------------------------------------------
            |
            | TransactionService:
            |
            | - Controla la jornada.
            | - Valida saldos.
            | - Descuenta dinero.
            | - Registra transacciones.
            | - Emite TransactionCreated después del commit.
            |
            */

                $firstTransactionId = null;

                foreach ($monetaryDeliveries as $item) {

                    $delivery = $item['delivery'];

                    $accountBalance = $item['account_balance'];

                    $transaction = $transactionService->create(
                        data: [
                            'account_balance_id' =>
                            $accountBalance->id,

                            'type' => 'expense',

                            'amount' => $delivery->amount,

                            'description' =>
                            'Entrega por liquidación de cheques - ' .
                                'Cliente: ' . $locked->client_name .
                                ' - Medio: ' .
                                (
                                    $delivery->isTransfer()
                                    ? 'Transferencia'
                                    : 'Efectivo'
                                ),

                            'date' => now(),

                            'destination_bank' =>
                            $item['destination_bank'],
                        ],

                        companyId: $companyId,

                        userId: auth()->id(),

                        source: 'cheque_liquidation',

                        externalId: $delivery->transactionExternalId()
                    );

                    /*
                 * Verificación de consistencia.
                 */

                    if (
                        (int) $transaction->company_id !== $companyId ||
                        $transaction->source !== 'cheque_liquidation' ||
                        $transaction->external_id !==
                        $delivery->transactionExternalId() ||
                        $transaction->type !== 'expense' ||
                        (int) $transaction->account_balance_id !==
                        (int) $accountBalance->id ||
                        (int) round(
                            (float) $transaction->amount * 100
                        ) !== (int) round(
                            (float) $delivery->amount * 100
                        )
                    ) {
                        throw new RuntimeException(
                            'El movimiento financiero no coincide con la entrega.'
                        );
                    }

                    $delivery->update([
                        'transaction_id' => $transaction->id,
                    ]);

                    if ($firstTransactionId === null) {
                        $firstTransactionId = $transaction->id;
                    }
                }

                /*
            |--------------------------------------------------------------------------
            | 9. Entregar cheques de cartera
            |--------------------------------------------------------------------------
            */

                foreach ($portfolioCheques as $portfolioCheque) {

                    $portfolioCheque->update([
                        'status' => Cheque::STATUS_DELIVERED,
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 10. Incorporar cheques recibidos
            |--------------------------------------------------------------------------
            */

                foreach ($cheques as $cheque) {

                    $cheque->update([
                        'status' => Cheque::STATUS_IN_PORTFOLIO,
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | 11. Confirmar liquidación
            |--------------------------------------------------------------------------
            |
            | transaction_id conserva la primera
            | transacción monetaria como referencia
            | de compatibilidad.
            |
            | Cada entrega monetaria posee además
            | su propio transaction_id.
            |
            | Si todo se pagó con cheques de terceros,
            | transaction_id permanece NULL.
            |
            */

                $locked->update([
                    'status' => 'confirmed',

                    'transaction_id' => $firstTransactionId,

                    'confirmed_at' => now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | 12. Historial
                |--------------------------------------------------------------------------
                */

                $locked->events()->create([
                    'user_id' => auth()->id(),

                    'action' => 'confirmed',

                    'from_status' => 'draft',

                    'to_status' => 'confirmed',
                ]);

                /*
                |--------------------------------------------------------------------------
                | 13. Notificar confirmación mediante Laravel Reverb
                |--------------------------------------------------------------------------
                |
                | El evento se envía únicamente después de que
                | MySQL confirma exitosamente la transacción.
                |
                | Esto garantiza que los demás usuarios no reciban
                | notificaciones de operaciones revertidas.
                |
                | TransactionCreated continúa notificando los
                | egresos monetarios por separado.
                |
                */

                $confirmedCompanyId = (int) $companyId;

                $confirmedLiquidationId = (int) $locked->id;

                $receivedChequesCount = $cheques->count();

                $deliveredChequesCount = count($portfolioCheques);

                $deliveriesCount = $deliveries->count();

                DB::afterCommit(function () use (
                    $confirmedCompanyId,
                    $confirmedLiquidationId,
                    $receivedChequesCount,
                    $deliveredChequesCount,
                    $deliveriesCount
                ) {

                    ChequeLiquidationConfirmed::dispatch(
                        companyId: $confirmedCompanyId,

                        liquidationId: $confirmedLiquidationId,

                        receivedChequesCount: $receivedChequesCount,

                        deliveredChequesCount: $deliveredChequesCount,

                        deliveriesCount: $deliveriesCount
                    );
                });
            }, 3);
        } catch (RuntimeException $error) {

            throw ValidationException::withMessages([
                'deliveries' => $error->getMessage(),
            ]);
        }

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                'Liquidación confirmada. Se procesaron los medios de pago y se actualizó la cartera de cheques.'
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
