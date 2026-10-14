@foreach ($accounts as $account)
    <a href="{{ route('accounts.movements', $account['id']) }}" class="bank-card-link">

        <div class="bank-card" data-account-id="{{ $account['id'] }}">


            {{-- =================================================
                        HEADER BANCO
                    ================================================== --}}

            <div class="bank-header">

                @if ($account['logo'])
                    <img src="{{ $account['logo'] }}" alt="{{ $account['name'] }}">
                @endif


                <div class="bank-header-content">

                    <h4>
                        {{ $account['name'] }}
                    </h4>


                    <small>

                        @switch($account['type'])
                            @case('bank')
                                Cuenta bancaria
                            @break

                            @case('wallet')
                                Billetera virtual
                            @break

                            @case('cash')
                                Efectivo
                            @break

                            @default
                                {{ ucfirst($account['type']) }}
                        @endswitch

                    </small>

                </div>

            </div>



            {{-- =================================================
                        SALDOS POR MONEDA
                    ================================================== --}}

            <div class="bank-currency-balances">

                @forelse ($account['balances'] as $balance)
                    <div class="bank-currency-balance" data-account-balance-id="{{ $balance['account_balance_id'] }}"
                        data-currency="{{ $balance['currency'] }}">


                        {{-- SALDO ACTUAL --}}

                        <div class="bank-balance" data-balance>

                            {{ $balance['currency'] }}

                            {{ number_format($balance['balance'], 2, ',', '.') }}

                        </div>



                        {{-- SALDO INICIAL --}}

                        <div class="bank-initial">

                            Inicial:

                            <span data-initial-balance>

                                {{ $balance['currency'] }}

                                {{ number_format($balance['initial_balance'], 2, ',', '.') }}

                            </span>

                        </div>



                        {{-- DATOS DE LA JORNADA --}}

                        <div class="bank-footer">


                            {{-- INGRESOS --}}

                            <span class="income" data-income title="Ingresos de la jornada">

                                <i class="bi bi-arrow-up"></i>

                                {{ number_format($balance['income'], 2, ',', '.') }}

                            </span>



                            {{-- EGRESOS --}}

                            <span class="expense" data-expense title="Egresos de la jornada">

                                <i class="bi bi-arrow-down"></i>

                                {{ number_format($balance['expense'], 2, ',', '.') }}

                            </span>



                            {{-- RESERVAS --}}

                            <span class="reserve" data-reserve title="Reservas de la jornada">

                                <i class="bi bi-lock"></i>

                                {{ number_format($balance['reserve'] ?? 0, 2, ',', '.') }}

                            </span>



                            {{-- MOVIMIENTOS --}}

                            <span class="bank-movements" data-movements title="Movimientos de la jornada">

                                <i class="bi bi-arrow-left-right"></i>

                                {{ $balance['movements'] ?? 0 }}

                            </span>

                        </div>


                    </div>


                @empty

                    <div class="bank-currency-empty">

                        Sin saldos configurados para esta jornada.

                    </div>
                @endforelse

            </div>


        </div>

    </a>
@endforeach
