@extends('layouts.app')

@section('content')

<div class="page-container clients-page">

    {{-- =====================================================
         HEADER
    ===================================================== --}}

    <div class="page-header">

        <div>
            <h1>
                <i class="bi bi-person-plus me-2"></i>
                Nuevo cliente
            </h1>

            <p>
                Registrá una persona física o jurídica
                en el padrón de tu empresa.
            </p>
        </div>

        <a href="{{ route('clients.index') }}"
           class="secondary-button">

            <i class="bi bi-arrow-left"></i>
            Volver al listado

        </a>

    </div>


    {{-- =====================================================
         ERRORES DE VALIDACIÓN
    ===================================================== --}}

    @if ($errors->any())

        <div class="alert-error mb-4">

            <i class="bi bi-exclamation-triangle"></i>

            <div>
                <strong>No se pudo guardar el cliente.</strong>

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
          action="{{ route('clients.store') }}">

        @csrf

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
                                @selected(old('person_type', 'individual') === 'individual')>
                                Persona física
                            </option>

                            <option value="company"
                                @selected(old('person_type') === 'company')>
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
                            value="{{ old('first_name') }}"
                            autocomplete="given-name"
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
                            value="{{ old('last_name') }}"
                            autocomplete="family-name"
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
                            value="{{ old('business_name') }}"
                            placeholder="Ej.: Comercial del Sur S.A.">

                    </div>

                </div>


                {{-- CUIT --}}

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
                            value="{{ old('tax_id') }}"
                            placeholder="20-12345678-9">

                        <small class="text-muted">
                            Opcional. Se validará automáticamente al guardar.
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
                            value="{{ old('document_number') }}"
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
                            value="{{ old('email') }}"
                            autocomplete="email"
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
                            value="{{ old('phone') }}"
                            autocomplete="tel"
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
                            value="{{ old('address') }}"
                            autocomplete="street-address"
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
                            value="{{ old('city') }}"
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
                            value="{{ old('province') }}"
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
                            value="{{ old('postal_code') }}"
                            placeholder="8000">

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             OBSERVACIONES Y ESTADO
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
                    placeholder="Información adicional del cliente...">{{ old('notes') }}</textarea>

            </div>

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
                    @checked(old('is_active', '1') == '1')>

                <label class="form-check-label"
                       for="is_active">
                    Cliente activo
                </label>

            </div>

        </div>


        {{-- =====================================================
             ACCIONES
        ===================================================== --}}

        <div class="form-actions">

            <a href="{{ route('clients.index') }}"
               class="secondary-button">

                <i class="bi bi-x-lg"></i>
                Cancelar

            </a>

            <button type="submit"
                    class="primary-action-button">

                <i class="bi bi-check-circle"></i>
                Guardar cliente

            </button>

        </div>

    </form>

</div>

@endsection
