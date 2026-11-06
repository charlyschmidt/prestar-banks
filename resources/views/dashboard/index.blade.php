@extends('layouts.app')


@section('content')

    <div class="page-container dashboard">


        {{-- =========================================================
        HEADER
    ========================================================== --}}

        <div class="page-header dashboard-page-header">

            <div>

                <h1>
                    Jornada actual
                </h1>


                <p id="last-update">

                    Jornada abierta:
                    {{ $summary['day']->date->format('d/m/Y') }}

                    · Desde:

                    {{ $summary['day']->opened_at->format('H:i') }}


                    @if ($summary['movimiento_ultimo'])
                        · Último movimiento:

                        {{ \Carbon\Carbon::parse($summary['movimiento_ultimo']->date)->format('H:i') }}

                        ·

                        {{ $summary['movimiento_ultimo']->description }}

                        ·

                        {{ $summary['movimiento_ultimo']->account->name }}


                        @if ($summary['movimiento_ultimo']->accountBalance)
                            ·

                            {{ $summary['movimiento_ultimo']->accountBalance->currency }}
                        @endif
                    @else
                        · Sin movimientos todavía
                    @endif

                </p>

            </div>

        </div>



        {{-- =========================================================
        BANCOS
    ========================================================== --}}

        <div class="section-title banks-section-title">

            <h1>
                Mis cuentas
            </h1>

        </div>



        <div class="banks-grid">
            @include('dashboard.partials.account-cards', [
                'accounts' => $summary['accounts'],
            ])
        </div>



        {{-- =========================================================
        MOVIMIENTOS
    ========================================================== --}}

        <div class="movements dashboard-movements">


            <div class="section-title">

                <h3>
                    Movimientos de la jornada
                </h3>

            </div>



            <div class="table-responsive">

                <table class="modern-table">


                    <thead>

                        <tr>
                            <th>#</th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Concepto
                            </th>

                            <th>
                                Banco
                            </th>

                            <th>
                                Moneda
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Monto
                            </th>

                            <th>
                                Saldo inicial
                            </th>

                            <th>
                                Saldo después
                            </th>

                        </tr>

                    </thead>



                    <tbody id="movements-body">

                        @forelse ($summary['movements'] as $movement)
                            @php

                                $currency = $movement->accountBalance?->currency ?? '---';

                            @endphp


                            <tr data-transaction-id="{{ $movement->id }}"
                                data-account-balance-id="{{ $movement->account_balance_id }}">

                                {{-- conteo --}}

                                <td data-movement-counter>
                                    {{ $loop->count - $loop->index }}
                                </td>


                                {{-- FECHA --}}

                                <td>

                                    <div class="movement-date">

                                        <strong>

                                            {{ \Carbon\Carbon::parse($movement->date)->format('d/m') }}

                                        </strong>

                                        <small>

                                            {{ \Carbon\Carbon::parse($movement->date)->format('H:i') }}

                                        </small>

                                    </div>

                                </td>



                                {{-- USUARIO / ORIGEN --}}

                                <td>

                                    <div class="movement-user">

                                        @if ($movement->source === 'api')
                                            <i class="bi bi-plug"></i>

                                            <span>
                                                {{ $movement->apiKey?->name ?? 'Integración API' }}
                                            </span>
                                        @elseif ($movement->source === 'import')
                                            <i class="bi bi-file-earmark-arrow-up"></i>

                                            <span>
                                                Importación
                                            </span>
                                        @else
                                            <i class="bi bi-person-circle"></i>

                                            <span>
                                                {{ $movement->user?->email ? \Illuminate\Support\Str::before($movement->user->email, '@') : 'Sin registro' }}
                                            </span>
                                        @endif

                                    </div>

                                </td>




                                {{-- CONCEPTO / CLIENTE --}}

                                <td>

                                    <strong>
                                        {{ $movement->description ?? 'Sin descripción' }}
                                    </strong>

                                    @if ($movement->client)
                                        <div class="text-muted small mt-1">

                                            <i class="bi bi-person me-1"></i>

                                            {{ $movement->client->display_name }}

                                        </div>
                                    @endif

                                </td>




                                {{-- BANCO --}}

                                <td>

                                    <div class="movement-bank">

                                        <a href="{{ route('accounts.movements', $movement->account) }}"
                                            class="account-cell-link">

                                            {{ $movement->account->name }}

                                        </a>

                                        <div class="execution-badge" data-execution-badge="{{ $movement->id }}"
                                            @if (!$movement->executed_at) hidden @endif>

                                            <i class="bi bi-check-circle-fill"></i>

                                            <span>
                                                Ejecutada
                                            </span>

                                        </div>

                                    </div>
                                </td>


                                {{-- MONEDA --}}

                                <td>

                                    <span class="movement-currency">

                                        {{ $currency }}

                                    </span>

                                </td>



                                {{-- TIPO --}}

                                <td>


                                    @if ($movement->type === 'income')
                                        <span class="tag income-tag">

                                            <i class="bi bi-arrow-up"></i>

                                            Ingreso

                                        </span>
                                    @elseif ($movement->type === 'reserve')
                                        <span class="tag reserve-tag">

                                            <i class="bi bi-lock"></i>

                                            Reserva

                                        </span>
                                    @else
                                        <span class="tag expense-tag">

                                            <i class="bi bi-arrow-down"></i>

                                            Egreso

                                        </span>
                                    @endif


                                </td>



                                {{-- MONTO --}}

                                <td class="amount">

                                    @if ($movement->type === 'income')
                                        <span class="amount-income">

                                            +
                                            {{ number_format($movement->amount, 2, ',', '.') }}

                                        </span>
                                    @else
                                        <span class="amount-expense">

                                            -
                                            {{ number_format($movement->amount, 2, ',', '.') }}

                                        </span>
                                    @endif

                                </td>



                                {{-- SALDO INICIAL --}}

                                <td>

                                    {{ number_format($movement->initial_balance ?? 0, 2, ',', '.') }}

                                </td>



                                {{-- SALDO DESPUÉS --}}

                                <td>

                                    <strong>
                                        {{ number_format($movement->balance_after ?? 0, 2, ',', '.') }}
                                    </strong>

                                    @if ((float) ($movement->transfer_tax_amount ?? 0) > 0)
                                        <div class="text-muted tax-mov">

                                            incl. imp. débito
                                            -
                                            {{ number_format($movement->transfer_tax_amount, 2, ',', '.') }}

                                        </div>
                                    @endif

                                </td>


                            </tr>


                        @empty


                            <tr data-empty-row>

                                <td colspan="10" class="text-center">

                                    Sin movimientos todavía.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>


                </table>

            </div>


        </div>


    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const grid = document.querySelector('.banks-grid');

            if (!grid) {
                return;
            }

            let scheduled = false;

            /*
            |--------------------------------------------------------------------------
            | Calcular altura de cada tarjeta
            |--------------------------------------------------------------------------
            */

            function resizeCards() {

                scheduled = false;

                const cards = grid.querySelectorAll('.bank-card-link');

                if (!cards.length) {
                    return;
                }

                const styles = window.getComputedStyle(grid);

                const rowHeight = parseFloat(styles.gridAutoRows);
                const rowGap = parseFloat(styles.rowGap) || 0;

                if (!rowHeight || !Number.isFinite(rowHeight)) {
                    return;
                }

                cards.forEach(function(card) {

                    const content = card.querySelector('.bank-card');

                    if (!content) {
                        return;
                    }

                    const height = content.getBoundingClientRect().height;

                    const span = Math.max(
                        1,
                        Math.ceil(
                            (height + rowGap) /
                            (rowHeight + rowGap)
                        )
                    );

                    card.style.setProperty(
                        '--masonry-span',
                        span
                    );

                });

            }

            /*
            |--------------------------------------------------------------------------
            | Evitar cálculos repetidos en un mismo frame
            |--------------------------------------------------------------------------
            */

            function scheduleResize() {

                if (scheduled) {
                    return;
                }

                scheduled = true;

                requestAnimationFrame(resizeCards);
            }

            /*
            |--------------------------------------------------------------------------
            | Activar masonry
            |--------------------------------------------------------------------------
            */

            grid.classList.add('is-masonry');

            scheduleResize();

            /*
            |--------------------------------------------------------------------------
            | Cambios en el tamaño del navegador
            |--------------------------------------------------------------------------
            */

            window.addEventListener('resize', scheduleResize);

            /*
            |--------------------------------------------------------------------------
            | Cambios de altura en las tarjetas
            |--------------------------------------------------------------------------
            |
            | Si una tarjeta crece o disminuye, por ejemplo
            | cuando tiene varias monedas, se recalcula.
            |
            */

            if ('ResizeObserver' in window) {

                const observer = new ResizeObserver(scheduleResize);

                grid.querySelectorAll('.bank-card').forEach(function(card) {
                    observer.observe(card);
                });

                observer.observe(grid);
            }

            /*
            |--------------------------------------------------------------------------
            | Nuevas cuentas agregadas dinámicamente
            |--------------------------------------------------------------------------
            */

            if ('MutationObserver' in window) {

                const mutationObserver = new MutationObserver(function() {

                    scheduleResize();

                });

                mutationObserver.observe(grid, {
                    childList: true
                });

            }

            /*
            |--------------------------------------------------------------------------
            | Imágenes de logos
            |--------------------------------------------------------------------------
            */

            grid.querySelectorAll('img').forEach(function(img) {

                if (!img.complete) {
                    img.addEventListener('load', scheduleResize, {
                        once: true
                    });
                }

            });

            /*
            |--------------------------------------------------------------------------
            | Ajuste inicial
            |--------------------------------------------------------------------------
            */

            requestAnimationFrame(function() {
                scheduleResize();
            });

        });
    </script>

@endsection
