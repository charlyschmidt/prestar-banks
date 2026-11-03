<?php

namespace App\Http\Controllers;

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
    private function activeCompanyId(): int
    {
        abort_unless(auth()->check(), 401);

        $companyId = (int) app(CompanyContextService::class)->id();
        abort_if($companyId <= 0, 403);

        return $companyId;
    }

    private function ensureLiquidationCompany(ChequeLiquidation $liquidation): int
    {
        $companyId = $this->activeCompanyId();
        abort_unless((int) $liquidation->company_id === $companyId, 404);

        return $companyId;
    }

    public function index(): View
    {
        $this->activeCompanyId();
        $liquidations = ChequeLiquidation::query()
            ->with('cheques')
            ->orderByDesc('id')
            ->paginate(20);

        return view('cheques.index', compact('liquidations'));
    }

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

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_name' => [
                'required',
                'string',
                'max:150',
            ],
            'operation_date' => [
                'required',
                'date_format:Y-m-d',
            ],
            'due_date' => [
                'required',
                'date_format:Y-m-d',
                'after:operation_date',
            ],
            'format' => [
                'required',
                'in:paper,echeq',
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
        ]);
    }

    private function calculate(
        array $form,
        ChequeLiquidationCalculator $calculator
    ): array {
        try {
            return $calculator->calculate($form);
        } catch (InvalidArgumentException $error) {
            throw ValidationException::withMessages([
                'nominal_amount' => $error->getMessage(),
            ]);
        }
    }

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

    public function store(
        Request $request,
        ChequeLiquidationCalculator $calculator
    ): RedirectResponse {
        $this->activeCompanyId();
        $form = $this->validated($request);

        $amounts = $this->calculate(
            $form,
            $calculator
        );

        $liquidation = DB::transaction(function () use (
            $form,
            $amounts
        ) {
            $liquidation = ChequeLiquidation::create(
                array_merge($amounts, [
                    'user_id' => auth()->id(),
                    'client_name' => trim($form['client_name']),
                    'operation_date' => $form['operation_date'],
                    'status' => 'draft',
                ])
            );

            $liquidation->cheques()->create([
                'kind' => 'third_party',
                'format' => $form['format'],
                'bank' => $form['bank'] ?? null,
                'issuer' => $form['issuer'] ?? null,
                'number' => $form['number'] ?? null,
                'due_date' => $form['due_date'],
                'nominal_amount' => $amounts['nominal_amount'],
                'status' => 'draft',
            ]);

            $liquidation->events()->create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'from_status' => null,
                'to_status' => 'draft',
            ]);

            return $liquidation;
        });

        return redirect()
            ->route('cheques.show', $liquidation)
            ->with(
                'success',
                'Liquidación guardada como borrador. No se registraron movimientos de fondos.'
            );
    }

    public function show(
        ChequeLiquidation $liquidation
    ): View {
        $companyId = $this->ensureLiquidationCompany($liquidation);

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
                ->whereHas('account', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
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

    public function confirm(
        Request $request,
        ChequeLiquidation $liquidation,
        TransactionService $transactionService
    ): RedirectResponse {

        $companyId = $this->ensureLiquidationCompany($liquidation);

        $data = $request->validate([
            'account_balance_id' => [
                'required',
                'integer',
                Rule::exists('account_balances', 'id')
                    ->where('company_id', $companyId),
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

        $account = \App\Models\Account::withoutGlobalScopes()
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
            !in_array($account->type, ['bank', 'wallet'], true)
        ) {
            throw ValidationException::withMessages([
                'account_balance_id' =>
                'Para transferir seleccioná una cuenta bancaria o billetera.',
            ]);
        }

        $destinationBank = null;

        if ($paymentMethod === 'transfer') {

            $destinationBank = trim(
                (string) ($data['destination_bank'] ?? '')
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

                $cheques = $locked->cheques()
                    ->lockForUpdate()
                    ->get();

                if (
                    $cheques->count() !== 1 ||
                    $cheques->first()->status !== 'draft'
                ) {
                    throw ValidationException::withMessages([
                        'status' =>
                        'La liquidación debe tener un único cheque pendiente.',
                    ]);
                }

                $transaction = $transactionService->create(
                    data: [
                        'account_balance_id' =>
                        $accountBalance->id,

                        'type' => 'expense',

                        'amount' =>
                        $locked->net_amount,

                        'description' =>
                        'Entrega de fondos por operación de cheque',

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
                    (int) $transaction->company_id !== $companyId ||
                    $transaction->source !== 'cheque_liquidation' ||
                    $transaction->external_id !== (string) $locked->id ||
                    $transaction->type !== 'expense' ||
                    (int) $transaction->account_balance_id !==
                    (int) $accountBalance->id ||
                    round((float) $transaction->amount, 2) !==
                    round((float) $locked->net_amount, 2)
                ) {
                    throw new RuntimeException(
                        'El movimiento no coincide con la liquidación.'
                    );
                }

                $locked->update([
                    'status' => 'confirmed',

                    'transaction_id' =>
                    $transaction->id,

                    'confirmed_at' => now(),
                ]);

                $cheques->first()->update([
                    'status' => 'in_portfolio',
                ]);

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
                'Operación confirmada. Se registró el egreso y el cheque ingresó en cartera.'
            );
    }

    public function hold(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        return $this->changeStatus(
            $liquidation,
            'draft',
            'held'
        );
    }

    public function release(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        return $this->changeStatus(
            $liquidation,
            'held',
            'draft'
        );
    }

    private function changeStatus(
        ChequeLiquidation $liquidation,
        string $from,
        string $to
    ): RedirectResponse {
        $companyId = $this->ensureLiquidationCompany($liquidation);

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

    public function destroy(
        ChequeLiquidation $liquidation
    ): RedirectResponse {
        $companyId = $this->ensureLiquidationCompany($liquidation);

        DB::transaction(function () use ($liquidation, $companyId) {

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