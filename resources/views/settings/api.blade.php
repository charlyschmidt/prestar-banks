@extends('layouts.app')


@section('content')

    <div class="page-container api-settings-page">


        {{-- =====================================================
            HEADER
        ====================================================== --}}

        <div class="page-header">

            <div>

                <h1>
                    Integraciones API
                </h1>

                <p>
                    Conectá AERIA Finance con tu ERP o sistema de gestión.
                </p>

            </div>


            <a href="{{ route('settings.index') }}" class="secondary-button">
                <i class="bi bi-arrow-left"></i>

                Volver a configuración
            </a>

        </div>



        {{-- =====================================================
            NUEVA API KEY
        ====================================================== --}}

        <div class="form-card api-card api-create-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-key"></i>
                </div>

                <div>

                    <h3>
                        Nueva API Key
                    </h3>

                    <p>
                        Generá una clave para conectar un ERP o sistema externo con AERIA Finance.
                    </p>

                </div>

            </div>


            <form method="POST" action="{{ route('settings.api-keys.store') }}">

                @csrf


                <div class="api-create-row">

                    <div class="settings-field api-name-field">

                        <label for="api_key_name" class="settings-label">
                            Nombre de la integración
                        </label>

                        <div class="api-input-row">

                            <input type="text" id="api_key_name" name="name" class="settings-input"
                                value="{{ old('name') }}" placeholder="Ej: Zeus ERP" maxlength="100" required>

                            <button type="submit" class="primary-action-button api-generate-button">
                                <i class="bi bi-plus-lg"></i>
                                Generar API Key
                            </button>

                        </div>

                        <div class="settings-help">
                            Usá un nombre que te permita identificar qué sistema utiliza esta clave.
                        </div>

                        @error('name')
                            <div class="settings-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </form>

        </div>



        {{-- =====================================================
            CLAVES DE ACCESO
        ====================================================== --}}

        <div class="form-card api-card api-keys-card">

            <div class="settings-section-header">

                <div class="setting-icon">
                    <i class="bi bi-plug"></i>
                </div>

                <div>

                    <h3>
                        Claves de acceso
                    </h3>

                    <p>
                        Administrá las claves utilizadas por sistemas externos.
                    </p>

                </div>

            </div>



            @if ($apiKeys->isEmpty())
                <div class="api-empty">

                    <i class="bi bi-key"></i>

                    <div>

                        <strong>
                            Todavía no hay API Keys
                        </strong>

                        <span>
                            Generá una clave para comenzar a conectar sistemas externos.
                        </span>

                    </div>

                </div>
            @else
                <div class="api-table-wrapper">

                    <table class="api-table">

                        <thead>

                            <tr>

                                <th>
                                    Integración
                                </th>

                                <th>
                                    API Key
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Último uso
                                </th>

                                <th>
                                    Creada
                                </th>

                                <th class="api-table-action">
                                    Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach ($apiKeys as $apiKey)
                                <tr>

                                    {{-- INTEGRACIÓN --}}

                                    <td>

                                        <div class="api-integration-name">

                                            <i class="bi bi-box-arrow-in-right"></i>

                                            <strong>
                                                {{ $apiKey->name }}
                                            </strong>

                                        </div>

                                    </td>



                                    {{-- API KEY --}}

                                    <td>

                                        <code class="api-key-prefix">
                                            {{ $apiKey->key_prefix }}••••••••
                                        </code>

                                    </td>



                                    {{-- ESTADO --}}

                                    <td>

                                        @if ($apiKey->revoked_at)
                                            <span class="api-status api-status-revoked">
                                                Revocada
                                            </span>
                                        @else
                                            <span class="api-status api-status-active">
                                                Activa
                                            </span>
                                        @endif

                                    </td>



                                    {{-- ÚLTIMO USO --}}

                                    <td>

                                        @if ($apiKey->last_used_at)
                                            {{ $apiKey->last_used_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="api-muted">
                                                Nunca
                                            </span>
                                        @endif

                                    </td>



                                    {{-- CREADA --}}

                                    <td>

                                        {{ $apiKey->created_at->format('d/m/Y H:i') }}

                                    </td>



                                    {{-- ACCIÓN --}}

                                    <td class="api-table-action">

                                        @if (!$apiKey->revoked_at)
                                            <form method="POST" action="{{ route('settings.api-keys.destroy', $apiKey) }}"
                                                class="api-revoke-form">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="danger-action-button api-revoke-button">
                                                    <i class="bi bi-x-circle"></i>
                                                    Revocar
                                                </button>

                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('settings.api-keys.delete', $apiKey) }}"
                                                class="api-delete-form">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="danger-action-button api-revoke-button">
                                                    <i class="bi bi-trash3"></i>
                                                    Eliminar
                                                </button>

                                            </form>
                                        @endif

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="api-security-note">

                    <i class="bi bi-shield-lock"></i>

                    <span>
                        Por seguridad, AERIA no almacena las API Keys completas.
                        Si perdés una clave, deberás revocarla y generar una nueva.
                    </span>

                </div>
            @endif

        </div>



        {{-- =====================================================
            DATOS PARA JS
        ====================================================== --}}

        @if (session('generated_api_key'))
            <div id="generatedApiKeyData" data-api-key="{{ session('generated_api_key') }}"
                data-integration-name="{{ session('generated_api_key_name') }}" hidden></div>
        @endif


    </div>

@endsection
