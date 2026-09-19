@php

    /*
    |--------------------------------------------------------------------------
    | Normalizar totales del header
    |--------------------------------------------------------------------------
    |
    | Compatibilidad:
    |
    | Formato anterior:
    | 2800000
    |
    | Formato multimoneda:
    | [
    |     'ARS' => 2800000,
    |     'USD' => 20000,
    | ]
    |
    */

    $headerBalance = $header['balance'] ?? [];
    $headerIncome = $header['income'] ?? [];
    $headerExpense = $header['expense'] ?? [];

    if (!is_array($headerBalance)) {
        $headerBalance = [
            'ARS' => (float) $headerBalance,
        ];
    }

    if (!is_array($headerIncome)) {
        $headerIncome = [
            'ARS' => (float) $headerIncome,
        ];
    }

    if (!is_array($headerExpense)) {
        $headerExpense = [
            'ARS' => (float) $headerExpense,
        ];
    }

@endphp


<header class="top-header">


    {{-- ==========================
         MOBILE TOP BAR
    ========================== --}}

    <div class="mobile-header-bar">

        <button type="button" class="mobile-menu-button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
            aria-controls="mobileSidebar" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>


        <div class="mobile-header-brand">

            <a href="{{ route('dashboard') }}" class="mobile-header-brand-link" aria-label="Ir al dashboard">

                @if ($activeCompany?->logo)
                    <img src="{{ asset('storage/' . $activeCompany->logo) }}" alt="{{ $activeCompany->name }}"
                        class="mobile-company-logo">
                @else
                    <span class="mobile-aeria-brand">
                        AERIA <span>Finance</span>
                    </span>
                @endif

            </a>

        </div>


        {{-- USUARIO MOBILE --}}

        <div class="dropdown user-menu mobile-user-menu">

            <button type="button" class="user-avatar user-avatar-button" data-bs-toggle="dropdown"
                aria-expanded="false" title="Menú de usuario">
                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username, 0, 1)) }}
            </button>


            <div class="dropdown-menu dropdown-menu-end user-dropdown">

                <div class="user-dropdown-header">

                    <div class="user-dropdown-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username, 0, 1)) }}
                    </div>

                    <div>

                        <strong>
                            {{ auth()->user()->name ?? auth()->user()->username }}
                        </strong>


                        @if (auth()->user()->isSuperAdmin())
                            <span>
                                Super Admin
                            </span>
                        @elseif (auth()->user()->isAdministration())
                            <span>
                                Administración
                            </span>
                        @else
                            <span>
                                Operador
                            </span>
                        @endif

                    </div>

                </div>


                <div class="user-dropdown-divider"></div>


                <a href="{{ route('dashboard') }}" class="user-dropdown-link">
                    <i class="bi bi-grid"></i>
                    Dashboard
                </a>


                <a href="{{ route('transactions.index') }}" class="user-dropdown-link">
                    <i class="bi bi-arrow-left-right"></i>
                    Movimientos
                </a>


                @if (auth()->user()->isSuperAdmin())
                    <a href="{{ route('history.index') }}" class="user-dropdown-link">
                        <i class="bi bi-clock-history"></i>
                        Historial
                    </a>


                    <a href="{{ route('settings.index') }}" class="user-dropdown-link">
                        <i class="bi bi-gear"></i>
                        Configuración
                    </a>
                @endif


                <div class="user-dropdown-divider"></div>


                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button type="submit" class="user-dropdown-link user-dropdown-button">
                        <i class="bi bi-box-arrow-right"></i>
                        Salir
                    </button>

                </form>

            </div>

        </div>

    </div>



    {{-- ==========================
         BALANCE
    ========================== --}}

    <div class="header-balance-row">

        <div class="header-balance">

            <span>
                Balance
            </span>


            <strong class="header-currency-list header-currency-list-balance" data-header-balance>

                @php
                    $nonZeroBalances = collect($header['balance'] ?? [])->filter(fn($amount) => (float) $amount != 0);
                @endphp

                @forelse ($nonZeroBalances as $currency => $amount)
                    <span class="header-currency-value">
                        {{ $currency }}
                        {{ number_format((float) $amount, 2, ',', '.') }}
                    </span>

                @empty

                    <span class="header-currency-value">
                        0,00
                    </span>
                @endforelse

            </strong>

        </div>


        {{-- BOTÓN NUEVO MOVIMIENTO MOBILE --}}

        <a href="{{ route('transactions.create') }}" class="mobile-new-movement" aria-label="Nuevo movimiento"
            title="Nuevo movimiento">
            <i class="bi bi-plus-lg"></i>
        </a>

    </div>



    {{-- ==========================
         BUSCADOR GLOBAL
    ========================== --}}

    @if (auth()->user()->isSuperAdmin())
        <form action="{{ route('history.index') }}" method="GET" class="header-global-search">

            <i class="bi bi-search"></i>

            <input type="text" name="search" value="{{ request()->routeIs('history.*') ? request('search') : '' }}"
                placeholder="Buscar movimientos..." autocomplete="off">

            <input type="hidden" name="results" value="1">

        </form>
    @endif



    {{-- ==========================
         ACCIONES DESKTOP
    ========================== --}}

    <div class="header-actions">


        {{-- INGRESOS --}}

        <div class="header-item">

            <span>
                Ingresos hoy
            </span>


            <strong class="text-income header-currency-list" data-header-income>

                @php
                    $nonZeroIncome = collect($header['income'] ?? [])->filter(fn($amount) => (float) $amount != 0);
                @endphp

                @forelse ($nonZeroIncome as $currency => $amount)
                    <span class="header-currency-value">
                        + {{ $currency }}
                        {{ number_format((float) $amount, 2, ',', '.') }}
                    </span>

                @empty

                    <span class="header-currency-value">
                        + 0,00
                    </span>
                @endforelse

            </strong>

        </div>



        {{-- EGRESOS --}}

        <div class="header-item">

            <span>
                Egresos hoy
            </span>


            <strong class="text-expense header-currency-list" data-header-expense>

                @php
                    $nonZeroExpense = collect($header['expense'] ?? [])->filter(fn($amount) => (float) $amount != 0);
                @endphp

                @forelse ($nonZeroExpense as $currency => $amount)
                    <span class="header-currency-value">
                        - {{ $currency }}
                        {{ number_format((float) $amount, 2, ',', '.') }}
                    </span>

                @empty

                    <span class="header-currency-value">
                        - 0,00
                    </span>
                @endforelse

            </strong>

        </div>



        {{-- NUEVO MOVIMIENTO DESKTOP --}}

        <a href="{{ route('transactions.create') }}" class="new-movement-button">

            <i class="bi bi-plus-lg"></i>

            <span>
                Nuevo movimiento
            </span>

        </a>



        {{-- ==========================
             USUARIO DESKTOP
        ========================== --}}

        <div class="user-profile-badge desktop-user-profile">


            @if (auth()->user()->isSuperAdmin())
                <span class="role-badge admin">
                    SUPER ADMIN
                </span>
            @elseif (auth()->user()->isAdministration())
                <span class="role-badge administration">
                    ADMINISTRACIÓN
                </span>
            @else
                <span class="role-badge user">
                    OPERADOR
                </span>
            @endif



            <div class="dropdown user-menu">

                <button type="button" class="user-avatar user-avatar-button" data-bs-toggle="dropdown"
                    aria-expanded="false" title="Menú de usuario">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username, 0, 1)) }}
                </button>


                <div class="dropdown-menu dropdown-menu-end user-dropdown">

                    <div class="user-dropdown-header">

                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username, 0, 1)) }}
                        </div>

                        <div>

                            <strong>
                                {{ auth()->user()->name ?? auth()->user()->username }}
                            </strong>


                            @if (auth()->user()->isSuperAdmin())
                                <span>
                                    Super Admin
                                </span>
                            @elseif (auth()->user()->isAdministration())
                                <span>
                                    Administración
                                </span>
                            @else
                                <span>
                                    Operador
                                </span>
                            @endif

                        </div>

                    </div>


                    <div class="user-dropdown-divider"></div>


                    <a href="{{ route('dashboard') }}" class="user-dropdown-link">
                        <i class="bi bi-grid"></i>
                        Dashboard
                    </a>


                    <a href="{{ route('transactions.index') }}" class="user-dropdown-link">
                        <i class="bi bi-arrow-left-right"></i>
                        Movimientos
                    </a>


                    @if (auth()->user()->isSuperAdmin())
                        <a href="{{ route('history.index') }}" class="user-dropdown-link">
                            <i class="bi bi-clock-history"></i>
                            Historial
                        </a>


                        <a href="{{ route('settings.index') }}" class="user-dropdown-link">
                            <i class="bi bi-gear"></i>
                            Configuración
                        </a>
                    @endif


                    <div class="user-dropdown-divider"></div>


                    <form method="POST" action="{{ route('logout') }}">

                        @csrf

                        <button type="submit" class="user-dropdown-link user-dropdown-button">
                            <i class="bi bi-box-arrow-right"></i>
                            Salir
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


</header>
