@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FORMATO MONETARIO
    |--------------------------------------------------------------------------
    */

    $money = static fn($amount) =>
        '$ ' . number_format(
            (float) $amount,
            2,
            ',',
            '.'
        );

    /*
    |--------------------------------------------------------------------------
    | FECHA ACTUAL
    |--------------------------------------------------------------------------
    */

    $today = now()->startOfDay();

@endphp

<div class="page-container cheques-page cheque-portfolio-page">

    {{-- ENCABEZADO --}}

    <div class="page-header">

        <div>
            <h1>Cartera de cheques</h1>

            <p>
                Cheques de terceros disponibles para
                nuevas operaciones.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">

            <a
                href="{{ route('cheques.index') }}"
                class="secondary-button text-decoration-none"
            >
                <i class="bi bi-arrow-left"></i>
                Liquidaciones
            </a>

            <a
                href="{{ route('cheques.create') }}"
                class="primary-action-button text-decoration-none"
            >
                <i class="bi bi-plus-lg"></i>
                Nueva liquidación
            </a>

        </div>

    </div>

    {{-- MENSAJES --}}

    @if (session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    {{-- INDICADORES DE CARTERA --}}

    <div class="cheque-portfolio-stats mb-4">

        {{-- CANTIDAD --}}

        <div class="cheque-portfolio-stat">

            <div class="cheque-portfolio-stat-icon">
                <i class="bi bi-file-earmark-check"></i>
            </div>

            <div>
                <span>
                    Cheques disponibles
                </span>

                <strong>
                    {{ number_format($chequeCount, 0, ',', '.') }}
                </strong>
            </div>

        </div>

        {{-- NOMINAL TOTAL --}}

        <div class="cheque-portfolio-stat">

            <div class="cheque-portfolio-stat-icon">
                <i class="bi bi-wallet2"></i>
            </div>

            <div>
                <span>
                    Nominal total en cartera
                </span>

                <strong>
                    {{ $money($totalNominal) }}
                </strong>
            </div>

        </div>

    </div>

    {{-- LISTADO --}}

    <section class="cheques-panel">

        <div
            class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"
        >

            <div class="cheques-section-title mb-0">
                Cheques disponibles
            </div>

            <span class="cheques-status cheques-status-in-portfolio">
                <i class="bi bi-check-circle"></i>
                En cartera
            </span>

        </div>

        <p class="cheques-note mb-3">
            Los cheques están ordenados por fecha
            de vencimiento, comenzando por los
            próximos a vencer.
        </p>

        <div class="table-responsive">

            <table class="table cheque-portfolio-table align-middle mb-0">

                <thead>

                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Banco</th>
                        <th scope="col">Número</th>
                        <th scope="col">Formato</th>
                        <th scope="col">Emisor</th>
                        <th scope="col">Cliente de origen</th>
                        <th scope="col">Vencimiento</th>
                        <th scope="col">Situación</th>
                        <th scope="col" class="text-end">
                            Nominal
                        </th>
                        <th scope="col" class="text-end">
                            Neto de adquisición
                        </th>
                        <th scope="col" class="text-end">
                            Acciones
                        </th>
                    </tr>

                </thead>

                <tbody>

                    @forelse ($cheques as $cheque)

                        @php
                            $dueDate = $cheque->due_date
                                ? $cheque->due_date->copy()->startOfDay()
                                : null;

                            $remainingDays = $dueDate
                                ? (int) $today->diffInDays($dueDate, false)
                                : null;

                            $isOverdue = $remainingDays !== null
                                && $remainingDays < 0;

                            $isToday = $remainingDays === 0;

                            $isNearDue = $remainingDays !== null
                                && $remainingDays > 0
                                && $remainingDays <= 7;

                            $originClient = $cheque->liquidation?->client_name
                                ?? 'Sin información';
                        @endphp

                        <tr>

                            {{-- IDENTIFICADOR --}}

                            <td>
                                <strong>
                                    #{{ $cheque->id }}
                                </strong>
                            </td>

                            {{-- BANCO --}}

                            <td>
                                {{ $cheque->bank ?: '—' }}
                            </td>

                            {{-- NÚMERO --}}

                            <td>
                                {{ $cheque->number ?: '—' }}
                            </td>

                            {{-- FORMATO --}}

                            <td>
                                {{ $cheque->format === 'echeq'
                                    ? 'Echeq'
                                    : 'Físico' }}
                            </td>

                            {{-- EMISOR --}}

                            <td>
                                {{ $cheque->issuer ?: '—' }}
                            </td>

                            {{-- CLIENTE ORIGEN --}}

                            <td>
                                {{ $originClient }}
                            </td>

                            {{-- VENCIMIENTO --}}

                            <td>
                                <strong>
                                    {{ $dueDate?->format('d/m/Y') ?? '—' }}
                                </strong>
                            </td>

                            {{-- SITUACIÓN --}}

                            <td>

                                @if ($isOverdue)

                                    <span class="cheque-portfolio-due cheque-portfolio-overdue">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Vencido hace {{ abs($remainingDays) }} días
                                    </span>

                                @elseif ($isToday)

                                    <span class="cheque-portfolio-due cheque-portfolio-today">
                                        <i class="bi bi-clock"></i>
                                        Vence hoy
                                    </span>

                                @elseif ($isNearDue)

                                    <span class="cheque-portfolio-due cheque-portfolio-near">
                                        <i class="bi bi-clock-history"></i>
                                        En {{ $remainingDays }} días
                                    </span>

                                @elseif ($remainingDays !== null)

                                    <span class="cheque-portfolio-due cheque-portfolio-normal">
                                        {{ $remainingDays }} días
                                    </span>

                                @else

                                    —
                                @endif

                            </td>

                            {{-- NOMINAL --}}

                            <td class="text-end">
                                <strong>
                                    {{ $money($cheque->nominal_amount) }}
                                </strong>
                            </td>

                            {{-- NETO DE ADQUISICIÓN --}}

                            <td class="text-end">

                                @if ($cheque->net_amount !== null)

                                    {{ $money($cheque->net_amount) }}

                                @else

                                    <span class="cheques-note">
                                        No registrado
                                    </span>

                                @endif

                            </td>

                            {{-- ACCIONES --}}

                            <td class="text-end">

                                @if ($cheque->liquidation)

                                    <a
                                        href="{{ route('cheques.show', $cheque->liquidation) }}"
                                        class="secondary-button cheques-small-button text-decoration-none"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Ver origen
                                    </a>

                                @else

                                    —

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <i class="bi bi-inbox fs-3 d-block mb-3"></i>

                                <strong>
                                    No hay cheques disponibles en cartera.
                                </strong>

                                <p class="cheques-note mt-2 mb-0">
                                    Los cheques ingresarán cuando
                                    confirmes una liquidación.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    {{-- PAGINACIÓN --}}

    @if ($cheques->hasPages())

        <div class="mt-3">
            {{ $cheques->links() }}
        </div>

    @endif

</div>

@endsection
