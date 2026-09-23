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

        <div class="mobile-header-actions">


            {{-- ALERTAS --}}

            <div class="dropdown header-alerts">

                <button type="button" class="header-alert-button" data-bs-toggle="dropdown" aria-expanded="false"
                    title="Alertas" aria-label="Alertas">

                    <i class="bi bi-bell"></i>

                    <span class="header-alert-count {{ ($header['alerts_count'] ?? 0) > 0 ? '' : 'd-none' }}"
                        data-header-alert-count>
                        {{ $header['alerts_count'] ?? 0 }}
                    </span>

                </button>


                <div class="dropdown-menu dropdown-menu-end header-alert-dropdown">


                    <div class="header-alert-dropdown-header">

                        <div>

                            <strong>
                                Alertas
                            </strong>

                            <span>
                                Avisos que requieren tu atención
                            </span>

                        </div>


                        <span class="header-alert-total {{ ($header['alerts_count'] ?? 0) > 0 ? '' : 'd-none' }}"
                            data-header-alert-total>
                            {{ $header['alerts_count'] ?? 0 }}
                        </span>

                    </div>


                    <div class="header-alert-dropdown-divider"></div>


                    <div class="header-alert-list" data-header-alert-list>


                        @forelse (($header['alerts'] ?? collect()) as $alert)
                            <a href="{{ route('accounts.alerts', $alert->account_id) }}" class="header-alert-item">

                                <div class="header-alert-item-icon">

                                    <i class="bi bi-exclamation-lg"></i>

                                </div>


                                <div class="header-alert-item-content">

                                    <strong>
                                        {{ $alert->account?->name ?? 'Cuenta' }}
                                    </strong>

                                    <span>
                                        Saldo bajo · {{ $alert->accountBalance?->currency }}
                                    </span>

                                    <small>
                                        Disponible:
                                        {{ $alert->accountBalance?->currency }}
                                        {{ number_format((float) $alert->current_balance, 2, ',', '.') }}
                                    </small>

                                    <small>
                                        Límite:
                                        {{ $alert->accountBalance?->currency }}
                                        {{ number_format((float) $alert->accountBalance?->low_balance_threshold, 2, ',', '.') }}
                                    </small>

                                </div>

                            </a>

                        @empty

                            @if (($header['reminders'] ?? collect())->isEmpty())
                                <div class="header-alert-empty" data-header-alert-empty>

                                    <div class="header-alert-empty-icon">
                                        <i class="bi bi-check-lg"></i>
                                    </div>

                                    <div>

                                        <strong>
                                            Sin alertas
                                        </strong>

                                        <span>
                                            No tenés avisos pendientes.
                                        </span>

                                    </div>

                                </div>
                            @endif
                        @endforelse


                        {{-- RECORDATORIOS VENCIDOS --}}

                        @foreach ($header['reminders'] ?? collect() as $reminder)
                            <a href="{{ route('reminders.index') }}" class="header-alert-item"
                                data-header-reminder="{{ $reminder->id }}">

                                <div class="header-alert-item-icon">

                                    <i class="bi bi-bell-fill"></i>

                                </div>


                                <div class="header-alert-item-content">

                                    <strong>
                                        {{ $reminder->title }}
                                    </strong>

                                    <span>
                                        Recordatorio pendiente
                                    </span>

                                    <small>
                                        {{ $reminder->scheduled_at->format('d/m/Y H:i') }}
                                    </small>

                                </div>

                            </a>
                        @endforeach


                    </div>

                </div>

            </div>


            {{-- USUARIO --}}

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


                    <a href="{{ route('dashboard') }}"
                        class="user-dropdown-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                        <i class="bi bi-grid"></i>
                        Dashboard

                    </a>


                    <a href="{{ route('accounts.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">

                        <i class="bi bi-wallet2"></i>
                        Cuentas

                    </a>


                    <a href="{{ route('transactions.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">

                        <i class="bi bi-arrow-left-right"></i>
                        Movimientos

                    </a>


                    <a href="{{ route('reminders.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('reminders.*') ? 'active' : '' }}">

                        <i class="bi bi-calendar2-check"></i>
                        Recordatorios

                    </a>


                    @if (auth()->user()->isSuperAdmin())
                        <div class="user-dropdown-divider"></div>


                        <a href="{{ route('history.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('history.*') ? 'active' : '' }}">

                            <i class="bi bi-clock-history"></i>
                            Historial

                        </a>


                        <a href="{{ route('users.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('users.*') ? 'active' : '' }}">

                            <i class="bi bi-people"></i>
                            Usuarios

                        </a>


                        <a href="{{ route('settings.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">

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

            <input type="text" name="search"
                value="{{ request()->routeIs('history.*') ? request('search') : '' }}"
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


            {{-- ==========================
                 ALERTAS
            ========================== --}}

            <div class="dropdown header-alerts">

                <button type="button" class="header-alert-button" data-bs-toggle="dropdown" aria-expanded="false"
                    title="Alertas" aria-label="Alertas">

                    <i class="bi bi-bell"></i>

                    <span class="header-alert-count {{ ($header['alerts_count'] ?? 0) > 0 ? '' : 'd-none' }}"
                        data-header-alert-count>
                        {{ $header['alerts_count'] ?? 0 }}
                    </span>

                </button>


                <div class="dropdown-menu dropdown-menu-end header-alert-dropdown">


                    <div class="header-alert-dropdown-header">

                        <div>

                            <strong>
                                Alertas
                            </strong>

                            <span>
                                Avisos que requieren tu atención
                            </span>

                        </div>


                        <span class="header-alert-total {{ ($header['alerts_count'] ?? 0) > 0 ? '' : 'd-none' }}"
                            data-header-alert-total>
                            {{ $header['alerts_count'] ?? 0 }}
                        </span>

                    </div>


                    <div class="header-alert-dropdown-divider"></div>


                    <div class="header-alert-list" data-header-alert-list>


                        @forelse (($header['alerts'] ?? collect()) as $alert)
                            <a href="{{ route('accounts.alerts', $alert->account_id) }}" class="header-alert-item">

                                <div class="header-alert-item-icon">

                                    <i class="bi bi-exclamation-lg"></i>

                                </div>


                                <div class="header-alert-item-content">

                                    <strong>
                                        {{ $alert->account?->name ?? 'Cuenta' }}
                                    </strong>

                                    <span>
                                        Saldo bajo · {{ $alert->accountBalance?->currency }}
                                    </span>

                                    <small>
                                        Disponible:
                                        {{ $alert->accountBalance?->currency }}
                                        {{ number_format((float) $alert->current_balance, 2, ',', '.') }}
                                    </small>

                                    <small>
                                        Límite:
                                        {{ $alert->accountBalance?->currency }}
                                        {{ number_format((float) $alert->accountBalance?->low_balance_threshold, 2, ',', '.') }}
                                    </small>

                                </div>

                            </a>

                        @empty

                            @if (($header['reminders'] ?? collect())->isEmpty())
                                <div class="header-alert-empty" data-header-alert-empty>

                                    <div class="header-alert-empty-icon">
                                        <i class="bi bi-check-lg"></i>
                                    </div>

                                    <div>

                                        <strong>
                                            Sin alertas
                                        </strong>

                                        <span>
                                            No tenés avisos pendientes.
                                        </span>

                                    </div>

                                </div>
                            @endif
                        @endforelse


                        {{-- RECORDATORIOS VENCIDOS --}}

                        @foreach ($header['reminders'] ?? collect() as $reminder)
                            <a href="{{ route('reminders.index') }}" class="header-alert-item"
                                data-header-reminder="{{ $reminder->id }}">

                                <div class="header-alert-item-icon">

                                    <i class="bi bi-bell-fill"></i>

                                </div>


                                <div class="header-alert-item-content">

                                    <strong>
                                        {{ $reminder->title }}
                                    </strong>

                                    <span>
                                        Recordatorio pendiente
                                    </span>

                                    <small>
                                        {{ $reminder->scheduled_at->format('d/m/Y H:i') }}
                                    </small>

                                </div>

                            </a>
                        @endforeach


                    </div>

                </div>

            </div>


            {{-- USUARIO --}}

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


                    <a href="{{ route('dashboard') }}"
                        class="user-dropdown-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                        <i class="bi bi-grid"></i>
                        Dashboard

                    </a>


                    <a href="{{ route('accounts.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">

                        <i class="bi bi-wallet2"></i>
                        Cuentas

                    </a>


                    <a href="{{ route('transactions.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">

                        <i class="bi bi-arrow-left-right"></i>
                        Movimientos

                    </a>


                    <a href="{{ route('reminders.index') }}"
                        class="user-dropdown-link {{ request()->routeIs('reminders.*') ? 'active' : '' }}">

                        <i class="bi bi-calendar2-check"></i>
                        Recordatorios

                    </a>


                    @if (auth()->user()->isSuperAdmin())
                        <div class="user-dropdown-divider"></div>


                        <a href="{{ route('history.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('history.*') ? 'active' : '' }}">

                            <i class="bi bi-clock-history"></i>
                            Historial

                        </a>


                        <a href="{{ route('users.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('users.*') ? 'active' : '' }}">

                            <i class="bi bi-people"></i>
                            Usuarios

                        </a>


                        <a href="{{ route('settings.index') }}"
                            class="user-dropdown-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">

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
