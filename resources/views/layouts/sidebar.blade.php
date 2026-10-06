{{-- ==========================
     SIDEBAR MOBILE
========================== --}}

<div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="mobileSidebar"
    aria-labelledby="mobileSidebarLabel">

    <div class="offcanvas-header">

        {{-- BRAND --}}
        <div class="sidebar-brand">

            <a href="{{ route('dashboard') }}">

                @if ($activeCompany?->logo)
                    <img src="{{ asset('storage/' . $activeCompany->logo) }}" alt="{{ $activeCompany->name }}"
                        class="sidebar-company-logo">
                @else
                    <div class="sidebar-aeria-brand">
                        AERIA <span>Finance</span>
                    </div>
                @endif

            </a>

            @if ($activeCompany)

                <div class="sidebar-company-name" id="mobileSidebarLabel">
                    {{ $activeCompany->name }}
                </div>

                @if ($activeCompany->hasActiveSubscription())

                    <div class="sidebar-subscription-badge is-active">
                        Suscripción activa
                    </div>
                @elseif ($activeCompany->isOnTrial())
                    @php
                        $trialDaysRemaining = max(
                            1,
                            (int) ceil(now()->diffInSeconds($activeCompany->trial_ends_at, false) / 86400),
                        );
                    @endphp

                    <div class="sidebar-subscription-trial">

                        <div class="sidebar-subscription-badge is-trial">

                            Período de prueba

                            <span class="sidebar-subscription-days">
                                {{ $trialDaysRemaining }}
                                {{ $trialDaysRemaining === 1 ? 'día' : 'días' }}
                            </span>

                        </div>

                    </div>

                @endif

            @endif

        </div>


        <button type="button" class="btn-close sidebar-close" data-bs-dismiss="offcanvas" aria-label="Cerrar">
        </button>

    </div>



    <div class="offcanvas-body p-0">

        <nav class="sidebar-nav">

            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

                <i class="bi bi-grid"></i>

                Dashboard

            </a>


            <a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.*') ? 'active' : '' }}">

                <i class="bi bi-wallet2"></i>

                Cuentas

            </a>


            <a href="{{ route('transactions.index') }}"
                class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}">

                <i class="bi bi-arrow-left-right"></i>

                Movimientos

            </a>


            <a href="{{ route('reminders.index') }}" class="{{ request()->routeIs('reminders.*') ? 'active' : '' }}">

                <i class="bi bi-calendar2-check"></i>

                Recordatorios

            </a>


            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">

                    <i class="bi bi-clock-history"></i>

                    Historial

                </a>


                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">

                    <i class="bi bi-people"></i>

                    Usuarios

                </a>


                {{-- CONFIGURACIÓN --}}
                <details class="sidebar-menu-group" {{ request()->routeIs('settings.*') ? 'open' : '' }}>

                    <summary class="sidebar-menu-parent {{ request()->routeIs('settings.*') ? 'active' : '' }}">

                        <span class="sidebar-menu-parent-content">
                            <i class="bi bi-gear"></i>
                            Configuración
                        </span>

                        <i class="bi bi-chevron-down sidebar-menu-chevron"></i>

                    </summary>


                    <div class="sidebar-submenu">

                        <a href="{{ route('settings.index') }}"
                            class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">

                            <i class="bi bi-sliders"></i>

                            General

                        </a>


                        <a href="{{ route('settings.api') }}"
                            class="{{ request()->routeIs('settings.api') ? 'active' : '' }}">

                            <i class="bi bi-plug"></i>

                            Integraciones API

                        </a>

                        <a href="{{ route('settings.api.documentation') }}"
                            class="{{ request()->routeIs('settings.api.documentation') ? 'active' : '' }}">

                            <i class="bi bi-book"></i>

                            Documentación API

                        </a>

                    </div>

                </details>
            @endif


            <form method="POST" action="{{ route('logout') }}">

                @csrf

                <button type="submit" class="sidebar-link-button">

                    <i class="bi bi-box-arrow-right"></i>

                    Salir

                </button>

            </form>

        </nav>

    </div>

</div>



{{-- ==========================
     SIDEBAR DESKTOP
========================== --}}

<aside class="sidebar desktop-sidebar">

    {{-- BRAND --}}
    <div class="sidebar-brand">

        <a href="{{ route('dashboard') }}">

            @if ($activeCompany?->logo)
                <img src="{{ asset('storage/' . $activeCompany->logo) }}" alt="{{ $activeCompany->name }}"
                    class="sidebar-company-logo">
            @else
                <div class="sidebar-aeria-brand">
                    AERIA <span>Finance</span>
                </div>
            @endif

        </a>


        @if ($activeCompany)

            <div class="sidebar-company-name">
                {{ $activeCompany->name }}
            </div>


            @if ($activeCompany->hasActiveSubscription())

                <div class="sidebar-subscription-badge is-active">
                    Suscripción activa
                </div>
            @elseif ($activeCompany->isOnTrial())
                @php
                    $trialDaysRemaining = max(
                        1,
                        (int) ceil(now()->diffInSeconds($activeCompany->trial_ends_at, false) / 86400),
                    );
                @endphp

                <div class="sidebar-subscription-trial">

                    <div class="sidebar-subscription-badge is-trial">

                        Período de prueba

                        <span class="sidebar-subscription-days">
                            {{ $trialDaysRemaining }}
                            {{ $trialDaysRemaining === 1 ? 'día' : 'días' }}
                        </span>

                    </div>

                </div>

            @endif

        @endif

    </div>



    {{-- NAV --}}
    <nav>

        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

            <i class="bi bi-grid"></i>

            Dashboard

        </a>


        <a href="{{ route('accounts.index') }}" class="{{ request()->routeIs('accounts.*') ? 'active' : '' }}">

            <i class="bi bi-wallet2"></i>

            Cuentas

        </a>


        <a href="{{ route('transactions.index') }}"
            class="{{ request()->routeIs('transactions.*') ? 'active' : '' }}">

            <i class="bi bi-arrow-left-right"></i>

            Movimientos

        </a>


        <a href="{{ route('reminders.index') }}" class="{{ request()->routeIs('reminders.*') ? 'active' : '' }}">

            <i class="bi bi-calendar2-check"></i>

            Recordatorios

        </a>


        @if (auth()->user()->isSuperAdmin())
            <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">

                <i class="bi bi-clock-history"></i>

                Historial

            </a>


            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">

                <i class="bi bi-people"></i>

                Usuarios

            </a>


            {{-- CONFIGURACIÓN --}}
            <details class="sidebar-menu-group" {{ request()->routeIs('settings.*') ? 'open' : '' }}>

                <summary class="sidebar-menu-parent {{ request()->routeIs('settings.*') ? 'active' : '' }}">

                    <span class="sidebar-menu-parent-content">
                        <i class="bi bi-gear"></i>
                        Configuración
                    </span>

                    <i class="bi bi-chevron-down sidebar-menu-chevron"></i>

                </summary>


                <div class="sidebar-submenu">

                    <a href="{{ route('settings.index') }}"
                        class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">

                        <i class="bi bi-sliders"></i>

                        General

                    </a>


                    <a href="{{ route('settings.api') }}"
                        class="{{ request()->routeIs('settings.api') ? 'active' : '' }}">

                        <i class="bi bi-plug"></i>

                        Integraciones API

                    </a>

                    <a href="{{ route('settings.api.documentation') }}"
                        class="{{ request()->routeIs('settings.api.documentation') ? 'active' : '' }}">

                        <i class="bi bi-book"></i>

                        Documentación API

                    </a>

                </div>

            </details>
        @endif


        <form method="POST" action="{{ route('logout') }}">

            @csrf

            <button type="submit" class="sidebar-link-button">

                <i class="bi bi-box-arrow-right"></i>

                Salir

            </button>

        </form>

    </nav>

</aside>
