<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function store(
        Request $request,
        TransactionService $transactionService
    ): JsonResponse {

        $companyId = (int) $request->attributes->get(
            'company_id'
        );


        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        try {

            $data = $request->validate([

                'external_id' => [
                    'required',
                    'string',
                    'max:191',
                ],


                'account_balance_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'account_balances',
                        'id'
                    )->where(
                        fn($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
                    ),
                ],

                'type' => [
                    'required',
                    'in:income,expense,reserve,transfer_in,transfer_out',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'date' => [
                    'required',
                    'date',
                ],

                'destination_bank' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

            ]);
        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Crear movimiento
        |--------------------------------------------------------------------------
        */
        $apiKey = $request->attributes->get('api_key');

        try {

            $transaction = $transactionService->create(
                data: $data,
                companyId: $companyId,
                userId: null,
                source: 'api',
                externalId: $data['external_id'],
                apiKeyId: $apiKey->id
            );
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Respuesta
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $transaction->id,
                'external_id' => $transaction->external_id,
                'account_id' => $transaction->account_id,
                'account_balance_id' => $transaction->account_balance_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->date?->toIso8601String(),
                'balance_after' => $transaction->balance_after,
                'source' => $transaction->source,
            ],
        ], 201);
    }

    public function bulk(
        Request $request,
        TransactionService $transactionService
    ): JsonResponse {

        $companyId = (int) $request->attributes->get('company_id');
        $apiKey = $request->attributes->get('api_key');


        /*
    |--------------------------------------------------------------------------
    | Validación general del lote
    |--------------------------------------------------------------------------
    */

        try {

            $request->validate([
                'transactions' => [
                    'required',
                    'array',
                    'min:1',
                    'max:100',
                ],
            ]);
        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'El lote enviado no es válido.',
                'errors' => $e->errors(),
            ], 422);
        }


        $results = [];
        $processed = 0;
        $failed = 0;


        /*
    |--------------------------------------------------------------------------
    | Procesar movimientos
    |--------------------------------------------------------------------------
    */

        foreach ($request->input('transactions') as $index => $item) {

            /*
        |--------------------------------------------------------------------------
        | Validar movimiento individual
        |--------------------------------------------------------------------------
        */

            $validator = validator($item, [

                'external_id' => [
                    'required',
                    'string',
                    'max:191',
                ],

                'account_balance_id' => [
                    'required',
                    'integer',

                    Rule::exists(
                        'account_balances',
                        'id'
                    )->where(
                        fn($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
                    ),
                ],

                'type' => [
                    'required',
                    'in:income,expense,reserve,transfer_in,transfer_out',
                ],

                'amount' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'date' => [
                    'required',
                    'date',
                ],

                'destination_bank' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

            ], [
                'account_balance_id.required' =>
                'Debe indicar la cuenta y moneda.',

                'account_balance_id.exists' =>
                'La cuenta o moneda seleccionada no existe.',

                'external_id.required' =>
                'El external_id es obligatorio.',

                'type.required' =>
                'El tipo de movimiento es obligatorio.',

                'type.in' =>
                'El tipo de movimiento no es válido.',

                'amount.required' =>
                'El importe es obligatorio.',

                'amount.numeric' =>
                'El importe debe ser numérico.',

                'amount.min' =>
                'El importe debe ser mayor a cero.',

                'date.required' =>
                'La fecha es obligatoria.',

                'date.date' =>
                'La fecha enviada no es válida.',
            ]);




            if ($validator->fails()) {

                $failed++;

                $results[] = [
                    'index' => $index,
                    'external_id' => $item['external_id'] ?? null,
                    'success' => false,
                    'message' => 'Los datos del movimiento no son válidos.',
                    'errors' => $validator->errors(),
                ];

                continue;
            }


            $data = $validator->validated();


            /*
        |--------------------------------------------------------------------------
        | Crear movimiento
        |--------------------------------------------------------------------------
        */

            try {

                $transaction = $transactionService->create(
                    data: $data,
                    companyId: $companyId,
                    userId: null,
                    source: 'api',
                    externalId: $data['external_id'],
                    apiKeyId: $apiKey->id
                );

                $processed++;

                $results[] = [
                    'index' => $index,
                    'external_id' => $transaction->external_id,
                    'success' => true,
                    'transaction_id' => $transaction->id,
                    'account_id' => $transaction->account_id,
                    'account_balance_id' => $transaction->account_balance_id,
                    'balance_after' => $transaction->balance_after,
                ];
            } catch (\Throwable $e) {

                $failed++;

                $results[] = [
                    'index' => $index,
                    'external_id' => $data['external_id'],
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Respuesta
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'success' => $failed === 0,

            'summary' => [
                'received' => count($request->input('transactions')),
                'processed' => $processed,
                'failed' => $failed,
            ],

            'results' => $results,
        ]);
    }
}
