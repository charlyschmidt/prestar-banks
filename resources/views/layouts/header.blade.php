<header class="top-header d-flex flex-column flex-lg-row gap-3">


    {{-- BALANCE PRINCIPAL --}}

    <div class="header-balance">


        <span>
            Balance
        </span>


        <strong data-header-balance>

            ${{ number_format($header['balance'], 2, ',', '.') }}

        </strong>


    </div>






    <div class="header-actions d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-3">





        <div class="header-item">


            <span>
                Ingresos hoy
            </span>


            <strong class="green" data-header-income>

                + ${{ number_format($header['income'], 2, ',', '.') }}

            </strong>


        </div>

        <div class="header-item">


            <span>
                Egresos hoy
            </span>


            <strong class="red" data-header-expense>


                - ${{ number_format($header['expense'], 2, ',', '.') }}


            </strong>


        </div>








        <a href="{{ route('transactions.create') }}" class="new-movement-button">


            <i class="bi bi-plus-lg"></i>


            <span>
                Nuevo movimiento
            </span>


        </a>








        <div class="user-profile-badge">


            @if (auth()->user()->is_admin)
                <span class="role-badge admin">
                    SUPER ADMIN
                </span>
            @elseif (auth()->user()->role === 'administration')
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

                            @if (auth()->user()->is_admin)
                                <span>
                                    Super Admin
                                </span>
                            @elseif (auth()->user()->role === 'administration')
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

                    @if (auth()->user()->is_admin)
                        <a href="{{ route('history.index') }}" class="user-dropdown-link">
                            <i class="bi bi-clock-history"></i>
                            Historial
                        </a>
                    @endif


                    <div class="user-dropdown-divider"></div>


                    <a href="{{ route('settings.index') }}" class="user-dropdown-link">
                        <i class="bi bi-gear"></i>

                        Configuración
                    </a>


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
