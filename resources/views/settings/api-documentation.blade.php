@extends('layouts.app')

@section('content')
    <div class="page-container api-settings-page">

        {{-- =====================================================
        HEADER
    ====================================================== --}}

        <div class="page-header">

            <div>
                <h1>Documentación API</h1>

                <p>
                    Guía para conectar un ERP o sistema externo con AERIA Finance.
                </p>
            </div>

            <a href="{{ route('settings.api') }}" class="secondary-button">
                <i class="bi bi-arrow-left"></i>
                Integraciones API
            </a>

        </div>


        {{-- =====================================================
        INTRODUCCIÓN
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-code-slash"></i>
                </div>

                <div>
                    <h3>API AERIA Finance</h3>

                    <p>
                        La API permite enviar movimientos financieros desde
                        sistemas externos hacia AERIA Finance.
                    </p>
                </div>

            </div>

            <div class="api-security-note">
                <i class="bi bi-info-circle"></i>

                <span>
                    Cada API Key pertenece a una empresa.
                    Los movimientos enviados mediante esa clave quedan
                    automáticamente asociados a esa empresa.
                </span>
            </div>

        </div>


        {{-- =====================================================
        AUTENTICACIÓN
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <div>
                    <h3>Autenticación</h3>

                    <p>
                        Todas las peticiones deben enviar una API Key
                        mediante Bearer Token.
                    </p>
                </div>

            </div>

            <div class="settings-field">

                <label class="settings-label">
                    Header
                </label>

                <pre><code>Authorization: Bearer aeria_TU_API_KEY</code></pre>

            </div>

            <div class="settings-field">

                <label class="settings-label">
                    Headers recomendados
                </label>

                <pre><code>Authorization: Bearer aeria_TU_API_KEY
Accept: application/json
Content-Type: application/json</code></pre>

            </div>

            <div class="api-security-note">

                <i class="bi bi-key"></i>

                <span>
                    La API Key completa se muestra una sola vez al generarla.
                    No debe incluirse en código público, repositorios ni aplicaciones cliente.
                </span>

            </div>

        </div>


        {{-- =====================================================
        OBTENER CUENTAS
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <h3>Obtener cuentas</h3>

                    <p>
                        Permite conocer las cuentas disponibles y los identificadores
                        necesarios para enviar movimientos.
                    </p>
                </div>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Endpoint
                </label>

                <pre><code>GET /api/v1/accounts</code></pre>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo de respuesta
                </label>

                <pre><code>{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Banco",
            "type": "bank",
            "balances": [
                {
                    "id": 1,
                    "currency": "ARS"
                }
            ]
        }
    ]
}</code></pre>

            </div>



        </div>


        {{-- =====================================================
        CREAR MOVIMIENTO
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>

                <div>
                    <h3>Crear movimiento</h3>

                    <p>
                        Registra un movimiento financiero dentro de AERIA.
                    </p>
                </div>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Endpoint
                </label>

                <pre><code>POST /api/v1/transactions</code></pre>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo
                </label>

                <pre><code>{
    "external_id": "ERP-000123",
    "account_balance_id": 1,
    "type": "income",
    "amount": 27500,
    "description": "Cobro factura 0001-00001234",
    "date": "2026-10-06 10:30:00"
}</code></pre>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Respuesta
                </label>

                <pre><code>{
    "success": true,
    "data": {
        "id": 262,
        "external_id": "ERP-000123",
        "account_id": 1,
        "account_balance_id": 1,
        "type": "income",
        "amount": "27500.00",
        "description": "Cobro factura 0001-00001234",
        "date": "2026-10-06T10:30:00-03:00",
        "balance_after": "235608.80",
        "source": "api"
    }
}</code></pre>

            </div>

            <div class="api-security-note">

                <i class="bi bi-info-circle"></i>

                <span>
                    Para crear un movimiento deben enviarse
                    <strong>account_balance_id</strong>.
                    Este identifica la moneda de la cuenta.
                </span>

            </div>


        </div>


        {{-- =====================================================
    CREAR MOVIMIENTOS EN LOTE
====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-stack"></i>
                </div>

                <div>
                    <h3>Crear movimientos en lote</h3>

                    <p>
                        Permite enviar varios movimientos a AERIA
                        en una única petición.
                    </p>
                </div>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Endpoint
                </label>

                <pre><code>POST /api/v1/transactions/bulk</code></pre>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo
                </label>

                <pre><code>{
    "transactions": [
        {
            "external_id": "ERP-000124",
            "account_balance_id": 1,
            "type": "income",
            "amount": 15000,
            "description": "Cobro factura 000124",
            "date": "2026-10-06 10:30:00"
        },
        {
            "external_id": "ERP-000125",
            "account_balance_id": 1,
            "type": "expense",
            "amount": 5000,
            "description": "Pago proveedor",
            "date": "2026-10-06 10:31:00"
        }
    ]
}</code></pre>

            </div>


            <div class="api-security-note">

                <i class="bi bi-info-circle"></i>

                <span>
                    Se pueden enviar hasta
                    <strong>100 movimientos por petición</strong>.
                    Cada movimiento se procesa individualmente.
                </span>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo de respuesta
                </label>

                <pre><code>{
    "success": true,
    "summary": {
        "received": 2,
        "processed": 2,
        "failed": 0
    },
    "results": [
        {
            "index": 0,
            "external_id": "ERP-000124",
            "success": true,
            "transaction_id": 271,
            "account_id": 1,
            "account_balance_id": 1,
            "balance_after": "343000.00"
        },
        {
            "index": 1,
            "external_id": "ERP-000125",
            "success": true,
            "transaction_id": 272,
            "account_id": 1,
            "account_balance_id": 1,
            "balance_after": "338000.00"
        }
    ]
}</code></pre>

            </div>


            <div class="api-security-note">

                <i class="bi bi-shield-check"></i>

                <span>
                    <strong>Idempotencia:</strong>
                    cada movimiento conserva su propio
                    <strong>external_id</strong>.
                    Si un movimiento ya fue registrado,
                    AERIA no vuelve a crearlo ni modifica
                    nuevamente el saldo.
                </span>

            </div>


            <div class="api-security-note">

                <i class="bi bi-exclamation-circle"></i>

                <span>
                    <strong>Procesamiento parcial:</strong>
                    si un movimiento del lote es inválido,
                    los demás movimientos válidos continúan procesándose.
                    El resultado de cada movimiento se informa
                    individualmente en <strong>results</strong>.
                </span>

            </div>

        </div>

        {{-- =====================================================
        CAMPOS
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-list-check"></i>
                </div>

                <div>
                    <h3>Campos del movimiento</h3>

                    <p>
                        Datos aceptados por el endpoint de movimientos.
                    </p>
                </div>

            </div>


            <div class="api-table-wrapper">

                <table class="api-table">

                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Tipo</th>
                            <th>Requerido</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td><code>external_id</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Identificador único del movimiento en el sistema externo.</td>
                        </tr>

                        <tr>
                            <td><code>account_balance_id</code></td>
                            <td>integer</td>
                            <td>Sí</td>
                            <td>Identifica el saldo y moneda de la cuenta.</td>
                        </tr>

                        <tr>
                            <td><code>type</code></td>
                            <td>string</td>
                            <td>Sí</td>
                            <td>Tipo de movimiento.</td>
                        </tr>

                        <tr>
                            <td><code>amount</code></td>
                            <td>decimal</td>
                            <td>Sí</td>
                            <td>Importe del movimiento. Debe ser mayor a cero.</td>
                        </tr>

                        <tr>
                            <td><code>description</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Descripción o concepto del movimiento.</td>
                        </tr>

                        <tr>
                            <td><code>date</code></td>
                            <td>datetime</td>
                            <td>Sí</td>
                            <td>Fecha y hora del movimiento.</td>
                        </tr>

                        <tr>
                            <td><code>destination_bank</code></td>
                            <td>string</td>
                            <td>No</td>
                            <td>Banco o destino asociado al egreso, cuando corresponda.</td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =====================================================
        TIPOS DE MOVIMIENTO
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-arrow-down-up"></i>
                </div>

                <div>
                    <h3>Tipos de movimiento</h3>

                    <p>
                        Valores admitidos actualmente por la API.
                    </p>
                </div>

            </div>


            <div class="api-table-wrapper">

                <table class="api-table">

                    <thead>
                        <tr>
                            <th>Valor</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td><code>income</code></td>
                            <td>Ingreso de dinero.</td>
                        </tr>

                        <tr>
                            <td><code>expense</code></td>
                            <td>Egreso de dinero.</td>
                        </tr>

                        <tr>
                            <td><code>reserve</code></td>
                            <td>Reserva de fondos.</td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =====================================================
        IDEMPOTENCIA
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <h3>Idempotencia</h3>

                    <p>
                        Evita movimientos duplicados cuando un sistema
                        reintenta una petición.
                    </p>
                </div>

            </div>


            <div class="api-security-note">

                <i class="bi bi-check-circle"></i>

                <span>
                    <strong>external_id</strong> debe identificar de forma única
                    cada movimiento enviado a AERIA dentro de la empresa.
                </span>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo
                </label>

                <pre><code>{
    "external_id": "ERP-000123",
    "account_balance_id": 1,
    "type": "income",
    "amount": 27500,
    "description": "Cobro factura",
    "date": "2026-10-06 10:30:00"
}</code></pre>

            </div>

        </div>


        {{-- =====================================================
        ERRORES
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>
                    <h3>Errores</h3>

                    <p>
                        La API utiliza códigos HTTP estándar y respuestas JSON.
                    </p>
                </div>

            </div>


            <div class="api-table-wrapper">

                <table class="api-table">

                    <thead>
                        <tr>
                            <th>HTTP</th>
                            <th>Significado</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td><code>401</code></td>
                            <td>API Key inexistente, inválida o revocada.</td>
                        </tr>

                        <tr>
                            <td><code>403</code></td>
                            <td>La empresa asociada a la API Key no se encuentra activa.</td>
                        </tr>

                        <tr>
                            <td><code>422</code></td>
                            <td>Datos inválidos, saldo insuficiente o no existe una jornada abierta.</td>
                        </tr>

                    </tbody>

                </table>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo: saldo insuficiente
                </label>

                <pre><code>{
    "success": false,
    "message": "Saldo insuficiente. Disponible: ARS 208.108,80"
}</code></pre>

            </div>


            <div class="settings-field">

                <label class="settings-label">
                    Ejemplo: validación
                </label>

                <pre><code>{
    "success": false,
    "message": "Los datos enviados no son válidos.",
    "errors": {
        "account_balance_id": [
            "validation.exists"
        ]
    }
}</code></pre>

            </div>

        </div>


        {{-- =====================================================
        FLUJO RECOMENDADO
    ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <h3>Flujo recomendado</h3>

                    <p>
                        Integración básica de un ERP con AERIA Finance.
                    </p>
                </div>

            </div>


            <div class="api-security-note">

                <!--<i class="bi bi-arrow-right-circle"></i>-->

                <div class="api-flow-steps">

                    <span>
                        <strong>1.</strong>
                        Crear en AERIA las cuentas y monedas que se desean controlar.
                    </span>

                    <span>
                        <strong>2.</strong>
                        Generar una API Key.
                    </span>

                    <span>
                        <strong>3.</strong>
                        Consultar las cuentas mediante la API.
                    </span>

                    <span>
                        <strong>4.</strong>
                        Relacionarlas con las cuentas equivalentes del ERP.
                    </span>

                    <span>
                        <strong>5.</strong>
                        Enviar los movimientos hacia AERIA.
                    </span>

                </div>

            </div>

        </div>

    </div>
@endsection
