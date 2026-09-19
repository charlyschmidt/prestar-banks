@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">
        

            <div>

                <h1>
                    Configuración
                </h1>

                <p>
                    Personalizá tu empresa y el comportamiento del dashboard.
                </p>

            </div>

        </div>



        {{-- ==========================
         APARIENCIA
    ========================== --}}

        <div class="form-card settings-card">

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


                <div class="settings-field">

                    <label
                        for="company_name"
                        class="settings-label mb-2"
                    >
                        Nombre de la empresa
                    </label>

                    <input
                        type="text"
                        id="company_name"
                        name="name"
                        class="settings-input"
                        value="{{ old('name', $company->name) }}"
                        maxlength="120"
                        required
                    >

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



        {{-- ==========================
         SONIDO
    ========================== --}}

        <div class="form-card settings-card">

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
