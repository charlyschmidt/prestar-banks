@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">

            <div>

                <h1>
                    Configuración
                </h1>

                <p>
                    Personalizá tu empresa y administrá sus datos.
                </p>

            </div>


            <a href="{{ route('settings.api') }}" class="primary-action-button">
                <i class="bi bi-plug"></i>

                Integraciones API
            </a>

        </div>



        {{-- =====================================================
        SETTINGS GRID
    ====================================================== --}}

        <div class="settings-layout">


            {{-- =================================================
            DATOS DE LA EMPRESA
        ================================================== --}}

            <div class="form-card settings-card settings-company-card">

                <div class="settings-section-header">

                    <div class="setting-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <div>

                        <h3>
                            Datos de la empresa
                        </h3>

                        <p>
                            Información comercial y de facturación de la empresa.
                        </p>

                    </div>

                </div>


                <form method="POST" action="{{ route('settings.company.update') }}" class="company-data-form">

                    @csrf
                    @method('PATCH')


                    <div class="settings-fields-grid">


                        {{-- RAZÓN SOCIAL --}}

                        <div class="settings-field">

                            <label for="billing_name" class="settings-label">
                                Razón social
                            </label>

                            <input type="text" id="billing_name" name="billing_name" class="settings-input"
                                value="{{ old('billing_name', $company->billing_name) }}" maxlength="150">

                            @error('billing_name')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- CUIT --}}

                        <div class="settings-field">

                            <label for="tax_id" class="settings-label">
                                CUIT
                            </label>

                            <input type="text" id="tax_id" name="tax_id" class="settings-input"
                                value="{{ old('tax_id', $company->tax_id) }}" maxlength="20" placeholder="30-12345678-9">

                            @error('tax_id')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- EMAIL --}}

                        <div class="settings-field">

                            <label for="company_email" class="settings-label">
                                Email
                            </label>

                            <input type="email" id="company_email" name="email" class="settings-input"
                                value="{{ old('email', $company->email) }}" maxlength="150">

                            @error('email')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- TELÉFONO --}}

                        <div class="settings-field">

                            <label for="phone" class="settings-label">
                                Teléfono
                            </label>

                            <input type="text" id="phone" name="phone" class="settings-input"
                                value="{{ old('phone', $company->phone) }}" maxlength="50">

                            @error('phone')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- DIRECCIÓN --}}

                        <div class="settings-field">

                            <label for="billing_address" class="settings-label">
                                Dirección
                            </label>

                            <input type="text" id="billing_address" name="billing_address" class="settings-input"
                                value="{{ old('billing_address', $company->billing_address) }}" maxlength="180">

                            @error('billing_address')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- CÓDIGO POSTAL --}}

                        <div class="settings-field">

                            <label for="billing_postal_code" class="settings-label">
                                Código postal
                            </label>

                            <input type="text" id="billing_postal_code" name="billing_postal_code" class="settings-input"
                                value="{{ old('billing_postal_code', $company->billing_postal_code) }}" maxlength="20">

                            @error('billing_postal_code')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- LOCALIDAD --}}

                        <div class="settings-field">

                            <label for="billing_city" class="settings-label">
                                Localidad
                            </label>

                            <input type="text" id="billing_city" name="billing_city" class="settings-input"
                                value="{{ old('billing_city', $company->billing_city) }}" maxlength="100">

                            @error('billing_city')
                                <div class="settings-error">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>



                        {{-- PROVINCIA --}}

                        <div class="settings-field">

                            <label for="billing_province" class="settings-label">
                                Provincia
                            </label>

                            <select id="billing_province" name="billing_province" class="settings-input">

                                <option value="">
                                    Seleccionar
                                </option>

                                @foreach ($provinces as $province)
                                    <option value="{{ $province->name }}" @selected(old('billing_province', $company->billing_province) === $province->name)>
                                        {{ $province->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                    </div>



                    {{-- CONDICIÓN FISCAL --}}

                    <div class="settings-field settings-field-full">

                        <label for="billing_tax_status" class="settings-label">
                            Condición fiscal
                        </label>

                        <select id="billing_tax_status" name="billing_tax_status" class="settings-input">

                            <option value="">
                                Seleccionar
                            </option>

                            <option value="responsable_inscripto" @selected(old('billing_tax_status', $company->billing_tax_status) === 'responsable_inscripto')>
                                Responsable inscripto
                            </option>

                            <option value="monotributista" @selected(old('billing_tax_status', $company->billing_tax_status) === 'monotributista')>
                                Monotributista
                            </option>

                            <option value="exento" @selected(old('billing_tax_status', $company->billing_tax_status) === 'exento')>
                                Exento
                            </option>

                            <option value="consumidor_final" @selected(old('billing_tax_status', $company->billing_tax_status) === 'consumidor_final')>
                                Consumidor final
                            </option>

                        </select>

                        @error('billing_tax_status')
                            <div class="settings-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="company-data-actions">

                        <button type="submit" class="primary-action-button">
                            <i class="bi bi-check2"></i>
                            Guardar datos
                        </button>


                        <label class="invoice-checkbox">

                            <input type="checkbox" name="requires_invoice" value="1" @checked(old('requires_invoice', $company->requires_invoice))>

                            <span>
                                Requiero factura
                            </span>

                        </label>

                    </div>

                </form>

            </div>



            {{-- =================================================
            IDENTIDAD VISUAL
        ================================================== --}}

            <div class="form-card settings-card settings-branding-card">

                <div class="settings-section-header">

                    <div class="setting-icon">
                        <i class="bi bi-palette"></i>
                    </div>

                    <div>

                        <h3>
                            Identidad visual
                        </h3>

                        <p>
                            Personalizá la apariencia del dashboard para esta empresa.
                        </p>

                    </div>

                </div>


                <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data"
                    class="branding-form">

                    @csrf
                    @method('PATCH')


                    {{-- NOMBRE --}}

                    <div class="settings-field">

                        <label for="company_name" class="settings-label mb-2">
                            Nombre de la empresa
                        </label>

                        <input type="text" id="company_name" name="name" class="settings-input"
                            value="{{ old('name', $company->name) }}" maxlength="120" required>

                        <div class="settings-help mt-2">
                            Este nombre se mostrará en el dashboard y al seleccionar una empresa.
                        </div>

                        @error('name')
                            <div class="settings-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>



                    {{-- COLOR --}}

                    <div class="branding-field">

                        <div class="branding-field-info">

                            <label for="background_color">
                                Color principal
                            </label>

                            <p>
                                Se utilizará como fondo principal del dashboard.
                                El color del texto se adapta automáticamente.
                            </p>

                        </div>


                        <div class="color-control">

                            <input type="color" id="background_color" name="background_color"
                                value="{{ $company->background_color ?? '#104072' }}" class="color-picker">

                            <span class="color-value" id="backgroundColorValue">
                                {{ strtoupper($company->background_color ?? '#104072') }}
                            </span>

                        </div>

                    </div>



                    {{-- LOGO --}}

                    <div class="branding-field">

                        <div class="branding-field-info">

                            <label for="logo">
                                Logo de la empresa
                            </label>

                            <p>
                                Se mostrará en el sidebar y al seleccionar una empresa.
                                Debe ser una imagen cuadrada, entre 200x200 y 500x500 px.
                            </p>

                        </div>


                        <div class="logo-control">

                            <div class="logo-preview">

                                @if ($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}"
                                        id="companyLogoPreview">
                                @else
                                    <div class="logo-placeholder" id="companyLogoPlaceholder">
                                        {{ strtoupper(substr($company->name, 0, 1)) }}
                                    </div>

                                    <img src="" alt="" id="companyLogoPreview" style="display:none;">
                                @endif

                            </div>


                            <label for="logo" class="logo-upload-button">

                                <i class="bi bi-upload"></i>

                                Elegir logo

                            </label>


                            <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp"
                                class="logo-file-input">

                        </div>

                    </div>


                    @error('background_color')
                        <div class="settings-error">
                            {{ $message }}
                        </div>
                    @enderror


                    @error('logo')
                        <div class="settings-error">
                            {{ $message }}
                        </div>
                    @enderror


                    <div class="branding-actions">

                        <button type="submit" class="primary-action-button">

                            <i class="bi bi-check2"></i>

                            Guardar apariencia

                        </button>

                    </div>

                </form>

            </div>



            {{-- =================================================
            SONIDO
        ================================================== --}}

            <div class="form-card settings-card settings-sound-card">

                <div class="setting-row">

                    <div class="setting-info">

                        <div class="setting-icon">
                            <i class="bi bi-volume-up"></i>
                        </div>

                        <div>

                            <h3>
                                Sonido en nuevos movimientos
                            </h3>

                            <p>
                                Reproduce un sonido suave cuando ingresa un movimiento
                                y el dashboard se actualiza en tiempo real.
                            </p>

                        </div>

                    </div>


                    <div class="form-check form-switch">

                        <input class="form-check-input" type="checkbox" role="switch" id="realtimeSound">

                    </div>

                </div>


                <div class="setting-test">

                    <button type="button" class="primary-action-button" id="testRealtimeSound">

                        <i class="bi bi-play-circle"></i>

                        Probar sonido

                    </button>

                </div>

            </div>



            {{-- =================================================
            REINICIAR JORNADA
        ================================================== --}}

            <div class="form-card settings-card danger-zone settings-danger-card">

                <div class="settings-section-header">

                    <div class="setting-icon danger-zone-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>

                    <div>

                        <h3>
                            Reiniciar jornada actual
                        </h3>

                        <p>
                            Elimina todos los movimientos y saldos registrados
                            en la jornada actual para poder iniciarla nuevamente.
                        </p>

                    </div>

                </div>


                <button type="button" class="danger-action-button" id="resetFinancialDayButton" data-bs-toggle="modal"
                    data-bs-target="#resetFinancialDayModal">

                    <i class="bi bi-arrow-counterclockwise"></i>

                    Reiniciar jornada

                </button>

            </div>


        </div>



        {{-- =====================================================
        MODAL REINICIAR JORNADA
    ====================================================== --}}

        <div class="modal fade" id="resetFinancialDayModal" tabindex="-1" aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content reset-day-modal">


                    <div class="modal-header">

                        <div>

                            <h5 class="modal-title">
                                Reiniciar jornada actual
                            </h5>

                            <p class="reset-day-modal-description">
                                Esta acción eliminará los movimientos y saldos
                                registrados durante la jornada actual.
                            </p>

                        </div>


                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                    </div>



                    <div class="modal-body">


                        <div class="reset-day-warning">

                            <i class="bi bi-exclamation-triangle"></i>

                            <span>
                                Esta acción no se puede deshacer.
                            </span>

                        </div>


                        <div class="reset-day-confirmation">

                            <span>
                                Para confirmar, ingresá este código:
                            </span>

                            <strong id="resetFinancialDayCode">
                                ----
                            </strong>

                        </div>


                        <input type="text" id="resetFinancialDayCodeInput" class="settings-input reset-day-code-input"
                            maxlength="4" inputmode="numeric" autocomplete="off" placeholder="Ingresá los 4 dígitos">

                    </div>



                    <div class="modal-footer">

                        <button type="button" class="secondary-button" data-bs-dismiss="modal">
                            Cancelar
                        </button>


                        <form method="POST" action="{{ route('financial-days.reset-current') }}">

                            @csrf
                            @method('DELETE')


                            <button type="submit" class="danger-action-button" id="confirmResetFinancialDayButton"
                                disabled>

                                <i class="bi bi-trash3"></i>

                                Reiniciar jornada

                            </button>

                        </form>

                    </div>


                </div>

            </div>

        </div>


    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {


            /*
            |--------------------------------------------------------------------------
            | Color
            |--------------------------------------------------------------------------
            */

            const colorInput =
                document.getElementById('background_color');

            const colorValue =
                document.getElementById('backgroundColorValue');


            if (colorInput && colorValue) {

                colorInput.addEventListener('input', function() {

                    colorValue.textContent =
                        this.value.toUpperCase();

                });

            }



            /*
            |--------------------------------------------------------------------------
            | Preview logo
            |--------------------------------------------------------------------------
            */

            const logoInput =
                document.getElementById('logo');

            const logoPreview =
                document.getElementById('companyLogoPreview');

            const logoPlaceholder =
                document.getElementById('companyLogoPlaceholder');


            if (logoInput && logoPreview) {

                logoInput.addEventListener('change', function() {

                    const file = this.files[0];

                    if (!file) {
                        return;
                    }


                    const reader = new FileReader();


                    reader.onload = function(event) {

                        logoPreview.src =
                            event.target.result;

                        logoPreview.style.display =
                            'block';


                        if (logoPlaceholder) {

                            logoPlaceholder.style.display =
                                'none';

                        }

                    };


                    reader.readAsDataURL(file);

                });

            }


        });
    </script>
@endsection
