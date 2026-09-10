<aside class="sidebar">

    <div class="logo">

        <a href="{{ route('dashboard') }}">

            <img
                src="{{ asset('images/logo.png') }}"
                alt="Finanzas"
            >

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


        <a href="#">

            <i class="bi bi-gear"></i>

            Configuración

        </a>

    </nav>

</aside>
