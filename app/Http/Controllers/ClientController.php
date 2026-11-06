<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\CompanyContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        private readonly CompanyContextService $companyContext
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Empresa activa
    |--------------------------------------------------------------------------
    */

    private function companyId(): int
    {
        $companyId = $this->companyContext->id();

        abort_unless($companyId !== null, 403);

        return $companyId;
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener cliente mediante UUID
    |--------------------------------------------------------------------------
    |
    | Nunca buscamos por ID numérico recibido desde la URL.
    |
    | Además del UUID, verificamos que el cliente
    | pertenezca a la empresa activa.
    |
    */

    private function findClient(string $uuid): Client
    {
        return Client::query()
            ->forCompany($this->companyId())
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $companyId = $this->companyId();

        $search = trim((string) $request->query('search', ''));

        $status = $request->query('status', 'active');

        if (!in_array($status, ['active', 'inactive', 'all'], true)) {
            $status = 'active';
        }

        $query = Client::query()
            ->forCompany($companyId)
            ->search($search);

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $clients = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('business_name')
            ->paginate(25)
            ->withQueryString();

        $totalClients = Client::query()
            ->forCompany($companyId)
            ->count();

        $activeClients = Client::query()
            ->forCompany($companyId)
            ->active()
            ->count();

        return view('clients.index', compact(
            'clients',
            'search',
            'status',
            'totalClients',
            'activeClients'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Crear cliente
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $this->companyId();

        $client = new Client([
            'person_type' => 'individual',
            'is_active' => true,
        ]);

        return view('clients.create', compact('client'));
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar cliente
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->companyId();

        $this->normalizeInput($request);

        $validated = $this->validateClient(
            $request,
            $companyId
        );

        $validated['company_id'] = $companyId;

        $client = DB::transaction(
            fn() => Client::create($validated)
        );

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente creado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Detalle del cliente
    |--------------------------------------------------------------------------
    */


    public function show(string $client): View
    {
        /*
        |--------------------------------------------------------------------------
        | Cliente de la empresa activa
        |--------------------------------------------------------------------------
        */

        $client = $this->findClient($client);

        /*
        |--------------------------------------------------------------------------
        | Historial de movimientos del cliente
        |--------------------------------------------------------------------------
        |
        | Incluye movimientos de todas las jornadas.
        |
        | Restringimos por:
        | - Empresa del cliente
        | - ID del cliente
        |
        | Cargamos relaciones para evitar consultas N+1.
        |
        */

        $movements = \App\Models\Transaction::query()
            ->where('company_id', $client->company_id)
            ->where('client_id', $client->id)
            ->with([
                'account',
                'accountBalance',
                'financialDay',
                'user',
            ])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(
                15,
                ['*'],
                'movements_page'
            )
            ->withQueryString();

        return view(
            'clients.show',
            compact(
                'client',
                'movements'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Editar cliente
    |--------------------------------------------------------------------------
    */

    public function edit(string $client): View
    {
        $client = $this->findClient($client);

        return view('clients.edit', compact('client'));
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar cliente
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        string $client
    ): RedirectResponse {

        $client = $this->findClient($client);

        $this->normalizeInput($request);

        $validated = $this->validateClient(
            $request,
            $client->company_id,
            $client->id
        );

        DB::transaction(function () use ($client, $validated) {
            $client->update($validated);
        });

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | Activar / desactivar
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(string $client): RedirectResponse
    {
        $client = $this->findClient($client);

        $client->update([
            'is_active' => !$client->is_active,
        ]);

        $message = $client->is_active
            ? 'Cliente activado correctamente.'
            : 'Cliente desactivado correctamente.';

        return redirect()
            ->route('clients.show', $client)
            ->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | Normalización de documentos
    |--------------------------------------------------------------------------
    */

    private function normalizeInput(Request $request): void
    {
        $taxId = preg_replace(
            '/\D+/',
            '',
            (string) $request->input('tax_id', '')
        );

        $documentNumber = preg_replace(
            '/\D+/',
            '',
            (string) $request->input('document_number', '')
        );

        $request->merge([
            'tax_id' => $taxId !== '' ? $taxId : null,
            'document_number' => $documentNumber !== ''
                ? $documentNumber
                : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    private function validateClient(
        Request $request,
        int $companyId,
        ?int $clientId = null
    ): array {

        $uniqueTaxId = Rule::unique('clients', 'tax_id')
            ->where('company_id', $companyId);

        if ($clientId !== null) {
            $uniqueTaxId->ignore($clientId);
        }

        $validated = $request->validate([
            'person_type' => [
                'required',
                Rule::in(['individual', 'company']),
            ],

            'first_name' => [
                'nullable',
                'required_if:person_type,individual',
                'string',
                'max:150',
            ],

            'last_name' => [
                'nullable',
                'required_if:person_type,individual',
                'string',
                'max:150',
            ],

            'business_name' => [
                'nullable',
                'required_if:person_type,company',
                'string',
                'max:200',
            ],

            'document_number' => [
                'nullable',
                'digits_between:6,12',
            ],

            'tax_id' => [
                'nullable',
                'digits:11',
                $uniqueTaxId,
                function ($attribute, $value, $fail) {
                    if ($value !== null && !$this->validCuit($value)) {
                        $fail('El CUIT/CUIL ingresado no es válido.');
                    }
                },
            ],

            'email' => [
                'nullable',
                'email',
                'max:200',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:60',
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'city' => [
                'nullable',
                'string',
                'max:150',
            ],

            'province' => [
                'nullable',
                'string',
                'max:150',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ], [
            'person_type.required' =>
            'Seleccioná el tipo de persona.',

            'first_name.required_if' =>
            'El nombre es obligatorio para personas físicas.',

            'last_name.required_if' =>
            'El apellido es obligatorio para personas físicas.',

            'business_name.required_if' =>
            'La razón social es obligatoria para personas jurídicas.',

            'tax_id.digits' =>
            'El CUIT/CUIL debe tener exactamente 11 dígitos.',

            'tax_id.unique' =>
            'Ya existe un cliente con este CUIT/CUIL en la empresa.',

            'document_number.digits_between' =>
            'El documento debe contener entre 6 y 12 dígitos.',

            'email.email' =>
            'Ingresá una dirección de correo electrónico válida.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Coherencia del tipo de persona
        |--------------------------------------------------------------------------
        */

        if ($validated['person_type'] === 'individual') {
            $validated['business_name'] = null;
        } else {
            $validated['first_name'] = null;
            $validated['last_name'] = null;
        }

        return $validated;
    }

    /*
    |--------------------------------------------------------------------------
    | Validación de CUIT / CUIL argentino
    |--------------------------------------------------------------------------
    */

    private function validCuit(string $cuit): bool
    {
        if (!preg_match('/^\d{11}$/', $cuit)) {
            return false;
        }

        $weights = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cuit[$i] * $weights[$i];
        }

        $remainder = $sum % 11;

        $checkDigit = 11 - $remainder;

        if ($checkDigit === 11) {
            $checkDigit = 0;
        } elseif ($checkDigit === 10) {
            $checkDigit = 9;
        }

        return $checkDigit === (int) $cuit[10];
    }
}
