@php

    $activeCompany = app(\App\Services\CompanyContextService::class)->company();

    /*
    |--------------------------------------------------------------------------
    | Color principal
    |--------------------------------------------------------------------------
    */

    $backgroundColor = $activeCompany?->background_color ?? '#104072';

    /*
    |--------------------------------------------------------------------------
    | Determinar si el fondo es claro u oscuro
    |--------------------------------------------------------------------------
    */

    $hex = ltrim($backgroundColor, '#');

    $red = hexdec(substr($hex, 0, 2));

    $green = hexdec(substr($hex, 2, 2));

    $blue = hexdec(substr($hex, 4, 2));

    /*
     * Luminosidad perceptual.
     */

    $luminance = ($red * 299 + $green * 587 + $blue * 114) / 1000;

    $isLight = $luminance > 160;

    /*
    |--------------------------------------------------------------------------
    | Paleta automática
    |--------------------------------------------------------------------------
    */

    if ($isLight) {
        $textColor = '#111111';

        $mutedColor = 'rgba(0, 0, 0, .55)';

        $borderColor = 'rgba(0, 0, 0, .10)';

        $cardColor = 'rgba(0, 0, 0, .045)';

        $hoverColor = 'rgba(0, 0, 0, .07)';

        $buttonColor = '#111111';

        $buttonTextColor = '#ffffff';
    } else {
        $textColor = '#ffffff';

        $mutedColor = 'rgba(255, 255, 255, .65)';

        $borderColor = 'rgba(255, 255, 255, .10)';

        $cardColor = 'rgba(0, 0, 0, .20)';

        $hoverColor = 'rgba(255, 255, 255, .08)';

        $buttonColor = '#ffffff';

        $buttonTextColor = '#111111';
    }

@endphp


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>
        {{ $activeCompany?->name ?? 'AERIA Finance' }}
        | AERIA Finance
    </title>


    @vite(['resources/js/app.js', 'resources/css/footer.css'])


    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <meta name="auth-user-id" content="{{ auth()->id() }}">
    {{-- ==========================
         TEMA DE EMPRESA
    ========================== --}}

    <style>
        :root {

            --company-bg: {{ $backgroundColor }};

            --company-card: {{ $cardColor }};

            --company-text: {{ $textColor }};

            --company-muted: {{ $mutedColor }};

            --company-border: {{ $borderColor }};

            --company-hover: {{ $hoverColor }};

            --company-button: {{ $buttonColor }};

            --company-button-text: {{ $buttonTextColor }};

        }
    </style>

</head>


<body data-create-transaction-url="{{ route('transactions.create') }}" data-company-id="{{ session('company_id') }}"
    data-theme="{{ $isLight ? 'light' : 'dark' }}">


    <div class="app-layout">


        {{-- ==========================
             SIDEBAR
        ========================== --}}

        @include('layouts.sidebar', [
            'activeCompany' => $activeCompany,
        ])



        {{-- ==========================
             MAIN AREA
        ========================== --}}

        <div class="main-area">


            {{-- ==========================
                 HEADER GLOBAL
            ========================== --}}

            @include('layouts.header', [
                'activeCompany' => $activeCompany,
            ])


            {{-- ==========================
                 CONTENT
            ========================== --}}

            <main class="main-content">


                @if (session('error'))
                    <div class="alert-error m-3 m-md-4">

                        <i class="bi bi-exclamation-triangle"></i>

                        {{ session('error') }}

                    </div>
                @endif


                @if (session('success'))
                    <div class="alert-success m-3 m-md-4">

                        <i class="bi bi-check-circle"></i>

                        {{ session('success') }}

                    </div>
                @endif


                @yield('content')


            </main>
            
            @include('partials.public-footer', [
                'compact' => true,
            ])

        </div>


    </div>

    <div id="reminder-toast-container" class="reminder-toast-container" aria-live="polite" aria-atomic="false"></div>
</body>

</html>
