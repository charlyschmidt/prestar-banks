<div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="mobileSidebar">


    <div class="offcanvas-header">


        <div class="logo">


            <a href="{{ route('dashboard') }}">


                <img src="{{ asset('images/logo.svg') }}" alt="Finanzas">


            </a>


        </div>



        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas">
        </button>


    </div>






    <div class="offcanvas-body p-0">


        <nav class="sidebar-nav">


            <a href="{{ route('dashboard') }}">


                <i class="bi bi-grid"></i>

                Dashboard


            </a>





            <a href="{{ route('accounts.index') }}">


                <i class="bi bi-wallet2"></i>

                Cuentas


            </a>





            <a href="{{ route('transactions.index') }}">


                <i class="bi bi-arrow-left-right"></i>

                Movimientos


            </a>

            <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                Historial
            </a>



            @if (auth()->user()->is_admin)
                <a href="{{ route('usuarios.index') }}">


                    <i class="bi bi-people"></i>

                    Usuarios


                </a>
            @endif






            <a href="#">


                <i class="bi bi-gear"></i>

                Configuración


            </a>







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









{{-- SIDEBAR DESKTOP --}}


<aside class="sidebar desktop-sidebar">


    <div class="logo">


        <a href="{{ route('dashboard') }}">


            <img src="{{ asset('images/logo.svg') }}" alt="Finanzas">


        </a>


    </div>





    <nav>


        <a href="{{ route('dashboard') }}">


            <i class="bi bi-grid"></i>

            Dashboard


        </a>





        <a href="{{ route('accounts.index') }}">


            <i class="bi bi-wallet2"></i>

            Cuentas


        </a>





        <a href="{{ route('transactions.index') }}">


            <i class="bi bi-arrow-left-right"></i>

            Movimientos


        </a>

        @if (auth()->user()->is_admin)
            <a href="{{ route('history.index') }}" class="{{ request()->routeIs('history.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                Historial
            </a>
        @endif





        @if (auth()->user()->is_admin)
            <a href="{{ route('usuarios.index') }}">


                <i class="bi bi-people"></i>

                Usuarios


            </a>
        @endif






        <a href="{{ route('settings.index') }}">
            <i class="bi bi-gear"></i>
            Configuración
        </a>







        <form method="POST" action="{{ route('logout') }}">


            @csrf



            <button type="submit" class="sidebar-link-button">


                <i class="bi bi-box-arrow-right"></i>

                Salir



            </button>



        </form>



    </nav>


</aside>
