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
    | FECHAS Y VENCIMIENTOS
    |--------------------------------------------------------------------------
    |
    | Estos indicadores utilizan la fecha nominal
    | del cheque. El cálculo de presentación bancaria
    | se implementará en el servicio correspondiente.
    |
    */

    $today = now()->startOfDay();

    $dueSituation = static function ($cheque) use ($today) {

        if (!$cheque->due_date) {
            return [
                'days' => null,
                'type' => 'unknown',
                'label' => 'Sin fecha',
                'icon' => 'bi-question-circle',
            ];
        }

        $dueDate = $cheque->due_date->copy()->startOfDay();

        $days = (int) $today->diffInDays($dueDate, false);

        if ($days < 0) {
            return [
                'days' => $days,
                'type' => 'overdue',
                'label' => 'Vencido hace ' . abs($days) . ' días',
                'icon' => 'bi-exclamation-triangle',
            ];
        }

        if ($days === 0) {
            return [
                'days' => 0,
                'type' => 'today',
                'label' => 'Vence hoy',
                'icon' => 'bi-exclamation-circle',
            ];
        }

        if ($days <= 3) {
            return [
                'days' => $days,
                'type' => 'near',
                'label' => 'Vence en ' . $days . ' días',
                'icon' => 'bi-bell',
            ];
        }

        if ($days <= 7) {
            return [
                'days' => $days,
                'type' => 'near',
                'label' => 'En ' . $days . ' días',
                'icon' => 'bi-clock-history',
            ];
        }

        return [
            'days' => $days,
            'type' => 'normal',
            'label' => $days . ' días',
            'icon' => 'bi-calendar',
        ];
    };

    /*
    |--------------------------------------------------------------------------
    | INDICADORES DE LA PÁGINA ACTUAL
    |--------------------------------------------------------------------------
    |
    | No se presentan como totales generales porque
    | el listado utiliza paginación.
    |
    */

    $currentPageCheques = $cheques->getCollection();

    $overdueOnPage = $currentPageCheques->filter(
        fn($cheque) =>
            $dueSituation($cheque)['type'] === 'overdue'
    )->count();

    $todayOnPage = $currentPageCheques->filter(
        fn($cheque) =>
            $dueSituation($cheque)['type'] === 'today'
    )->count();

    $nearOnPage = $currentPageCheques->filter(
        fn($cheque) =>
            $dueSituation($cheque)['type'] === 'near'
    )->count();

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

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- INDICADORES DE CARTERA --}}

    <div class="cheque-portfolio-stats mb-4">

        <div class="cheque-portfolio-stat">

            <div class="cheque-portfolio-stat-icon">
                <i class="bi bi-file-earmark-check"></i>
            </div>

            <div>
                <span>Cheques disponibles</span>

                <strong>
                    {{ number_format($chequeCount, 0, ',', '.') }}
                </strong>
            </div>

        </div>

        <div class="cheque-portfolio-stat">

            <div class="cheque-portfolio-stat-icon">
                <i class="bi bi-wallet2"></i>
            </div>

            <div>
                <span>Nominal total en cartera</span>

                <strong>
                    {{ $money($totalNominal) }}
                </strong>
            </div>

        </div>

    </div>

    {{-- AVISOS DE VENCIMIENTO --}}

    @if ($overdueOnPage > 0 || $todayOnPage > 0 || $nearOnPage > 0)

        <div class="alert alert-warning mb-4">

            <div class="d-flex align-items-start gap-3">

                <i class="bi bi-bell fs-5"></i>

                <div class="flex-grow-1">

                    <strong>
                        Atención a los vencimientos
                    </strong>

                    <p class="mb-2 mt-1">
                        Estos cheques requieren seguimiento
                        según su fecha nominal de vencimiento.
                    </p>

                    <div class="d-flex gap-3 flex-wrap">

                        @if ($overdueOnPage > 0)
                            <span>
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>{{ $overdueOnPage }}</strong>
                                vencidos
                            </span>
                        @endif

                        @if ($todayOnPage > 0)
                            <span>
                                <i class="bi bi-calendar-event"></i>
                                <strong>{{ $todayOnPage }}</strong>
                                vencen hoy
                            </span>
                        @endif

                        @if ($nearOnPage > 0)
                            <span>
                                <i class="bi bi-clock-history"></i>
                                <strong>{{ $nearOnPage }}</strong>
                                próximos a vencer
                            </span>
                        @endif

                    </div>

                    <small class="d-block mt-2">
                        Indicadores correspondientes únicamente
                        a los cheques de esta página.
                    </small>

                </div>

            </div>

        </div>

    @endif

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
            Los cheques están ordenados por fecha de vencimiento,
            comenzando por los próximos a vencer.
            Los cheques entregados a terceros y los depositados
            no forman parte de la cartera disponible.
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
                            $situation = $dueSituation($cheque);

                            $originClient =
                                $cheque->liquidation?->client_name
                                ?? 'Sin información';

                            $situationClass = match (
                                $situation['type']
                            ) {
                                'overdue' => 'cheque-portfolio-overdue',
                                'today' => 'cheque-portfolio-today',
                                'near' => 'cheque-portfolio-near',
                                default => 'cheque-portfolio-normal',
                            };
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

                            {{-- CLIENTE DE ORIGEN --}}

                            <td>
                                {{ $originClient }}
                            </td>

                            {{-- VENCIMIENTO --}}

                            <td>
                                <strong>
                                    {{ $cheque->due_date?->format('d/m/Y') ?? '—' }}
                                </strong>
                            </td>

                            {{-- SITUACIÓN --}}

                            <td>

                                <span
                                    class="cheque-portfolio-due {{ $situationClass }}"
                                >
                                    <i class="bi {{ $situation['icon'] }}"></i>

                                    {{ $situation['label'] }}
                                </span>

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

                                <div
                                    class="d-flex align-items-center justify-content-end gap-2 flex-wrap"
                                >

                                    @if ($cheque->liquidation)

                                        <a
                                            href="{{ route('cheques.show', $cheque->liquidation) }}"
                                            class="secondary-button cheques-small-button text-decoration-none"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Ver origen
                                        </a>

                                    @endif

                                </div>

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
