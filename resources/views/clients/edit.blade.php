@extends('layouts.app')

@section('content')

<div class="page-container clients-page">

    {{-- =====================================================
         HEADER
    ===================================================== --}}

    <div class="page-header">

        <div>
            <h1>
                <i class="bi bi-pencil-square me-2"></i>
                Editar cliente
            </h1>

            <p>
                {{ $client->display_name }}
            </p>
        </div>

        <a href="{{ route('clients.show', $client) }}"
           class="secondary-button">

            <i class="bi bi-arrow-left"></i>
            Volver al cliente

        </a>

    </div>


    {{-- =====================================================
         ERRORES
    ===================================================== --}}

    @if ($errors->any())

        <div class="alert-error mb-4">

            <i class="bi bi-exclamation-triangle"></i>

            <div>

                <strong>
                    No se pudieron guardar los cambios.
                </strong>

                <ul class="mb-0 mt-2">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        </div>

    @endif


    {{-- =====================================================
         FORMULARIO
    ===================================================== --}}

    <form method="POST"
          action="{{ route('clients.update', $client) }}">

        @csrf
        @method('PUT')


        {{-- =====================================================
             IDENTIFICACIÓN
        ===================================================== --}}

        <div class="form-card mb-4">

            <h4 class="mb-3">

                <i class="bi bi-person-vcard me-2"></i>
                Identificación

            </h4>

            <div class="row g-3">

                {{-- TIPO DE PERSONA --}}

                <div class="col-12">

                    <div class="form-group mb-0">

                        <label for="person_type">
                            Tipo de persona *
                        </label>

                        <select
                            id="person_type"
                            name="person_type"
                            class="dark-input"
                            required>

                            <option value="individual"
                                @selected(old('person_type', $client->person_type) === 'individual')>

                                Persona física

                            </option>

                            <option value="company"
                                @selected(old('person_type', $client->person_type) === 'company')>

                                Persona jurídica

                            </option>

                        </select>

                    </div>

                </div>


                {{-- NOMBRE --}}

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="first_name">
                            Nombre (persona física) *
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            class="dark-input"
                            maxlength="150"
                            autocomplete="given-name"
                            value="{{ old('first_name', $client->first_name) }}"
                            placeholder="Ej.: Juan">

                    </div>

                </div>


                {{-- APELLIDO --}}

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="last_name">
                            Apellido (persona física) *
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            class="dark-input"
                            maxlength="150"
                            autocomplete="family-name"
                            value="{{ old('last_name', $client->last_name) }}"
                            placeholder="Ej.: Pérez">

                    </div>

                </div>


                {{-- RAZÓN SOCIAL --}}

                <div class="col-12">

                    <div class="form-group mb-0">

                        <label for="business_name">
                            Razón social (persona jurídica) *
                        </label>

                        <input
                            type="text"
                            id="business_name"
                            name="business_name"
                            class="dark-input"
                            maxlength="200"
                            value="{{ old('business_name', $client->business_name) }}"
                            placeholder="Ej.: Comercial del Sur S.A.">

                    </div>

                </div>


                {{-- CUIT / CUIL --}}

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="tax_id">
                            CUIT / CUIL
                        </label>

                        <input
                            type="text"
                            id="tax_id"
                            name="tax_id"
                            class="dark-input"
                            maxlength="15"
                            inputmode="numeric"
                            value="{{ old('tax_id', $client->tax_id) }}"
                            placeholder="20-12345678-9">

                        <small class="text-muted">
                            Opcional. Debe ser válido y no estar
                            registrado en otro cliente de esta empresa.
                        </small>

                    </div>

                </div>


                {{-- DNI --}}

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="document_number">
                            DNI / Documento
                        </label>

                        <input
                            type="text"
                            id="document_number"
                            name="document_number"
                            class="dark-input"
                            maxlength="20"
                            inputmode="numeric"
                            value="{{ old('document_number', $client->document_number) }}"
                            placeholder="Ej.: 30123456">

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             CONTACTO
        ===================================================== --}}

        <div class="form-card mb-4">

            <h4 class="mb-3">

                <i class="bi bi-telephone me-2"></i>
                Datos de contacto

            </h4>

            <div class="row g-3">

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="email">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="dark-input"
                            maxlength="200"
                            autocomplete="email"
                            value="{{ old('email', $client->email) }}"
                            placeholder="cliente@ejemplo.com">

                    </div>

                </div>

                <div class="col-12 col-md-6">

                    <div class="form-group mb-0">

                        <label for="phone">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="dark-input"
                            maxlength="60"
                            autocomplete="tel"
                            value="{{ old('phone', $client->phone) }}"
                            placeholder="Ej.: 291 555-1234">

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             DOMICILIO
        ===================================================== --}}

        <div class="form-card mb-4">

            <h4 class="mb-3">

                <i class="bi bi-geo-alt me-2"></i>
                Domicilio

            </h4>

            <div class="row g-3">

                <div class="col-12">

                    <div class="form-group mb-0">

                        <label for="address">
                            Dirección
                        </label>

                        <input
                            type="text"
                            id="address"
                            name="address"
                            class="dark-input"
                            maxlength="255"
                            autocomplete="street-address"
                            value="{{ old('address', $client->address) }}"
                            placeholder="Calle y número">

                    </div>

                </div>


                <div class="col-12 col-md-5">

                    <div class="form-group mb-0">

                        <label for="city">
                            Localidad
                        </label>

                        <input
                            type="text"
                            id="city"
                            name="city"
                            class="dark-input"
                            maxlength="150"
                            value="{{ old('city', $client->city) }}"
                            placeholder="Ej.: Bahía Blanca">

                    </div>

                </div>


                <div class="col-12 col-md-5">

                    <div class="form-group mb-0">

                        <label for="province">
                            Provincia
                        </label>

                        <input
                            type="text"
                            id="province"
                            name="province"
                            class="dark-input"
                            maxlength="150"
                            value="{{ old('province', $client->province) }}"
                            placeholder="Ej.: Buenos Aires">

                    </div>

                </div>


                <div class="col-12 col-md-2">

                    <div class="form-group mb-0">

                        <label for="postal_code">
                            Código postal
                        </label>

                        <input
                            type="text"
                            id="postal_code"
                            name="postal_code"
                            class="dark-input"
                            maxlength="20"
                            value="{{ old('postal_code', $client->postal_code) }}"
                            placeholder="8000">

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             INFORMACIÓN ADICIONAL
        ===================================================== --}}

        <div class="form-card mb-4">

            <h4 class="mb-3">

                <i class="bi bi-card-text me-2"></i>
                Información adicional

            </h4>

            <div class="form-group">

                <label for="notes">
                    Observaciones
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    class="dark-input"
                    rows="4"
                    maxlength="5000"
                    placeholder="Información adicional del cliente...">{{ old('notes', $client->notes) }}</textarea>

            </div>


            {{-- ESTADO --}}

            <div class="form-check">

                <input
                    type="hidden"
                    name="is_active"
                    value="0">

                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    class="form-check-input"
                    @checked((string) old('is_active', $client->is_active ? '1' : '0') === '1')>

                <label
                    class="form-check-label"
                    for="is_active">

                    Cliente activo

                </label>

            </div>

        </div>


        {{-- =====================================================
             ACCIONES
        ===================================================== --}}

        <div class="form-actions">

            <a href="{{ route('clients.show', $client) }}"
               class="secondary-button">

                <i class="bi bi-x-lg"></i>
                Cancelar

            </a>

            <button type="submit"
                    class="primary-action-button">

                <i class="bi bi-check-circle"></i>
                Guardar cambios

            </button>

        </div>

    </form>

</div>

@endsection
