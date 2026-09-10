<header class="top-header">

    <div class="header-balance">

        <span>
            Balance
        </span>

        <strong data-header-balance>

            ${{ number_format($header['balance'], 0, ',', '.') }}

        </strong>

    </div>


    <div class="header-actions">

        <div class="header-item">

            <span>
                Ingresos hoy
            </span>

            <strong class="green" data-header-income>

                + ${{ number_format($header['income'], 0, ',', '.') }}

            </strong>

        </div>


        <div class="header-item">

            <span>
                Egresos hoy
            </span>

            <strong class="red" data-header-expense>

                - ${{ number_format($header['expense'], 0, ',', '.') }}

            </strong>

        </div>


        <a href="{{ route('transactions.create') }}" class="new-movement-button">

            <i class="bi bi-plus-lg"></i>

            <span>
                Nuevo movimiento
            </span>

        </a>


        <div class="user-avatar">

            C

        </div>

    </div>


</header>
