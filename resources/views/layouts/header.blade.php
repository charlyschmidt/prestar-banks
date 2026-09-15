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




            <div class="user-avatar">


                {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username, 0, 1)) }}


            </div>




        </div>






    </div>





</header>
