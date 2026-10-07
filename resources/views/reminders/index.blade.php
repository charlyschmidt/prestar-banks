@extends('layouts.app')


@section('content')


    <div class="page-container">


        {{-- =====================================================
    HEADER
    ====================================================== --}}

        <div class="page-header reminders-page-header">

            <div>

                <h1>
                    Recordatorios
                </h1>

                <p>
                    Organizá tareas, pagos y movimientos que necesites recordar.
                </p>

            </div>


            <button type="button" class="primary-action-button" data-bs-toggle="modal" data-bs-target="#newReminderModal">
                <i class="bi bi-plus-lg"></i>

                Nuevo recordatorio
            </button>

        </div>

        {{-- =====================================================
    PENDIENTES
    ====================================================== --}}

        <section class="reminders-section" data-reminders-pending-section>


            <div class="reminders-section-header">

                <div>

                    <h2>
                        Pendientes
                    </h2>

                    <span data-reminders-pending-count>
                        {{ $reminders->where('status', 'pending')->count() }}
                        recordatorios
                    </span>

                </div>

            </div>



            <div class="reminders-list" data-reminders-pending-list>


                @forelse ($reminders->where('status', 'pending')
                                                            as $reminder)
                    <div class="reminder-item {{ $reminder->isDue() ? 'is-due' : '' }}"
                        data-reminder-item="{{ $reminder->id }}" data-reminder-title="{{ $reminder->title }}"
                        data-reminder-scheduled="{{ $reminder->scheduled_at->toIso8601String() }}">


                        {{-- FECHA --}}

                        <div class="reminder-date">


                            <span class="reminder-date-day">

                                {{ $reminder->scheduled_at->format('d') }}

                            </span>


                            <span class="reminder-date-month">

                                {{ strtoupper($reminder->scheduled_at->locale('es')->translatedFormat('M')) }}

                            </span>


                        </div>



                        {{-- CONTENIDO --}}

                        <div class="reminder-content">


                            <div class="reminder-title-row">


                                <strong>
                                    {{ $reminder->title }}
                                </strong>


                                @if ($reminder->isDue())
                                    <span class="reminder-due-badge">

                                        <i class="bi bi-bell-fill"></i>

                                        Pendiente

                                    </span>
                                @endif


                            </div>



                            <div class="reminder-meta">


                                <span>

                                    <i class="bi bi-clock"></i>

                                    {{ $reminder->scheduled_at->format('H:i') }}

                                </span>


                                @if ($reminder->account)
                                    <span>

                                        <i class="bi bi-bank"></i>

                                        {{ $reminder->account->name }}

                                    </span>
                                @endif


                                @if ($reminder->accountBalance)
                                    <span>

                                        {{ $reminder->accountBalance->currency }}

                                    </span>
                                @endif


                                @if ($reminder->amount !== null)
                                    <span class="reminder-amount">

                                        @if ($reminder->accountBalance)
                                            {{ $reminder->accountBalance->currency }}
                                        @endif

                                        ${{ number_format($reminder->amount, 0, ',', '.') }}

                                    </span>
                                @endif


                            </div>


                        </div>



                        {{-- ACCIONES --}}

                        <div class="reminder-actions">


                            <form method="POST" action="{{ route('reminders.complete', $reminder->id) }}"
                                data-reminder-complete-form data-reminder-id="{{ $reminder->id }}">

                                @csrf
                                @method('PATCH')


                                <button type="submit" class="reminder-complete-button" title="Marcar como realizado">

                                    <i class="bi bi-check-lg"></i>

                                    <span>
                                        Realizado
                                    </span>

                                </button>

                            </form>



                            <form method="POST" action="{{ route('reminders.destroy', $reminder->id) }}">

                                @csrf
                                @method('DELETE')


                                <button type="submit" class="reminder-delete-button" title="Eliminar recordatorio">

                                    <i class="bi bi-trash3"></i>

                                </button>


                            </form>


                        </div>


                    </div>


                @empty


                    <div class="reminders-empty">


                        <div class="reminders-empty-icon">

                            <i class="bi bi-bell"></i>

                        </div>


                        <strong>
                            No tenés recordatorios pendientes
                        </strong>


                        <span>
                            Creá uno para recibir un aviso en la fecha que necesites.
                        </span>


                    </div>
                @endforelse


            </div>


        </section>



        {{-- =====================================================
    REALIZADOS
    ====================================================== --}}

        <section
            class="reminders-section reminders-completed-section {{ $reminders->where('status', 'completed')->isEmpty() ? 'd-none' : '' }}"
            data-reminders-completed-section>


            <div class="reminders-section-header">

                <div>

                    <h2>
                        Realizados
                    </h2>

                    <span data-reminders-completed-count>
                        {{ $reminders->where('status', 'completed')->count() }}
                        recordatorios
                    </span>

                </div>

            </div>


            <div class="reminders-list" data-reminders-completed-list>

                @foreach ($reminders->where('status', 'completed') as $reminder)
                    <div class="reminder-item is-completed" data-reminder-item="{{ $reminder->id }}">


                        <div class="reminder-completed-icon">

                            <i class="bi bi-check-lg"></i>

                        </div>


                        <div class="reminder-content">


                            <strong>
                                {{ $reminder->title }}
                            </strong>


                            <div class="reminder-meta">

                                <span>
                                    {{ $reminder->scheduled_at->format('d/m/Y H:i') }}
                                </span>

                            </div>


                        </div>


                        <div class="reminder-actions">


                            <form method="POST" action="{{ route('reminders.destroy', $reminder->id) }}">

                                @csrf
                                @method('DELETE')


                                <button type="submit" class="reminder-delete-button" title="Eliminar recordatorio">

                                    <i class="bi bi-trash3"></i>

                                </button>


                            </form>


                        </div>


                    </div>
                @endforeach


            </div>


        </section>



    </div>



    {{-- =========================================================
MODAL NUEVO RECORDATORIO
========================================================= --}}

    <div class="modal fade" id="newReminderModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content reminder-modal">


                <div class="modal-header reminder-modal-header">

                    <div>

                        <h2>
                            Nuevo recordatorio
                        </h2>

                        <p>
                            Te avisaremos cuando llegue la fecha programada.
                        </p>

                    </div>


                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>

                </div>



                <form method="POST" action="{{ route('reminders.store') }}" id="reminder-form">

                    @csrf


                    <div class="modal-body reminder-modal-body">


                        {{-- RECORDATORIO --}}

                        <div class="reminder-form-group">

                            <label for="reminder_title">
                                ¿Qué necesitás recordar?
                            </label>


                            <input type="text" id="reminder_title" name="title" value="{{ old('title') }}"
                                class="dark-input" placeholder="Ej: Transferir a Charly" maxlength="255" required>

                        </div>



                        {{-- FECHA --}}

                        <div class="reminder-form-group">

                            <label for="reminder_scheduled_at">
                                Fecha y hora
                            </label>


                            <input type="datetime-local" id="reminder_scheduled_at" name="scheduled_at"
                                value="{{ old('scheduled_at') }}" class="dark-input" required>

                        </div>

                        <div class="reminder-recurrence" id="reminder-recurrence" style="display:none;">

                            <div class="reminder-recurrence-title">
                                Repetir recordatorio
                            </div>

                            <div class="form-check form-switch reminder-recurrence-switch">

                                <label class="form-check-label" for="recurrence_weekly" id="reminder-weekly-label">
                                    Todas las semanas
                                </label>

                                <input class="form-check-input" type="checkbox" id="recurrence_weekly"
                                    name="recurrence_weekly" value="1">

                            </div>


                            <div class="form-check form-switch reminder-recurrence-switch">

                                <label class="form-check-label" for="recurrence_monthly" id="reminder-monthly-label">
                                    Todos los meses
                                </label>

                                <input class="form-check-input" type="checkbox" id="recurrence_monthly"
                                    name="recurrence_monthly" value="1">

                            </div>

                            <input type="hidden" name="recurrence_type" id="recurrence_type"
                                value="{{ old('recurrence_type') }}">

                        </div>

                        {{-- DATOS OPCIONALES --}}

                        <!--<div class="reminder-optional">


                                                                <div class="reminder-optional-title">

                                                                    <span>
                                                                        Información adicional
                                                                    </span>

                                                                    <small>
                                                                        Opcional
                                                                    </small>

                                                                </div>



                                                                {{-- CUENTA --}}

                                                                <div class="reminder-form-group">

                                                                    <label for="reminder_account">
                                                                        Cuenta
                                                                    </label>


                                                                    <select id="reminder_account" name="account_id" class="dark-input">

                                                                </div>



                                                                {{-- MONEDA --}}

                                                                <div class="reminder-form-group">

                                                                    <label for="reminder_balance">
                                                                        Moneda
                                                                    </label>


                                                                    <select id="reminder_balance" name="account_balance_id" class="dark-input" disabled>

                                                                        <option value="">
                                                                            Seleccioná una cuenta primero
                                                                        </option>

                                                                    </select>

                                                                </div>



                                                                {{-- IMPORTE --}}

                                                                <div class="reminder-form-group">

                                                                    <label for="reminder_amount">
                                                                        Importe
                                                                    </label>


                                                                    <input type="text" inputmode="decimal" id="reminder_amount" name="amount"
                                                                        value="{{ old('amount') }}" class="dark-input money-input" placeholder="0">

                                                                </div>


                                                            </div>-->


                    </div>



                    <div class="modal-footer reminder-modal-footer">


                        <button type="button" class="secondary-button" data-bs-dismiss="modal">
                            Cancelar
                        </button>


                        <button type="submit" class="primary-action-button">

                            <i class="bi bi-bell"></i>

                            Crear recordatorio

                        </button>


                    </div>


                </form>


            </div>

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /*
            |--------------------------------------------------------------------------
            | ELEMENTOS
            |--------------------------------------------------------------------------
            */

            const dateInput = document.getElementById(
                'reminder_scheduled_at'
            );

            const recurrenceBox = document.getElementById(
                'reminder-recurrence'
            );

            const weeklyLabel = document.getElementById(
                'reminder-weekly-label'
            );

            const monthlyLabel = document.getElementById(
                'reminder-monthly-label'
            );

            const weeklySwitch = document.getElementById(
                'recurrence_weekly'
            );

            const monthlySwitch = document.getElementById(
                'recurrence_monthly'
            );

            const recurrenceType = document.getElementById(
                'recurrence_type'
            );


            /*
            |--------------------------------------------------------------------------
            | DÍAS
            |--------------------------------------------------------------------------
            */

            const days = [
                'domingos',
                'lunes',
                'martes',
                'miércoles',
                'jueves',
                'viernes',
                'sábados'
            ];


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR OPCIONES SEGÚN FECHA
            |--------------------------------------------------------------------------
            */

            function updateRecurrenceOptions() {

                if (!dateInput.value) {

                    recurrenceBox.style.display = 'none';

                    return;
                }


                const dateParts = dateInput.value
                    .split('T')[0]
                    .split('-');


                const year = parseInt(dateParts[0]);

                const month = parseInt(dateParts[1]) - 1;

                const day = parseInt(dateParts[2]);


                const date = new Date(
                    year,
                    month,
                    day
                );


                const dayName = days[
                    date.getDay()
                ];


                weeklyLabel.textContent =
                    'Todos los ' + dayName;


                monthlyLabel.textContent =
                    'Todos los ' + day + ' de cada mes';


                recurrenceBox.style.display = 'block';
            }


            /*
            |--------------------------------------------------------------------------
            | RECURRENCIA SEMANAL
            |--------------------------------------------------------------------------
            */

            weeklySwitch.addEventListener('change', function() {

                if (this.checked) {

                    monthlySwitch.checked = false;

                    recurrenceType.value = 'weekly';

                } else {

                    recurrenceType.value = '';

                }

            });


            /*
            |--------------------------------------------------------------------------
            | RECURRENCIA MENSUAL
            |--------------------------------------------------------------------------
            */

            monthlySwitch.addEventListener('change', function() {

                if (this.checked) {

                    weeklySwitch.checked = false;

                    recurrenceType.value = 'monthly';

                } else {

                    recurrenceType.value = '';

                }

            });


            /*
            |--------------------------------------------------------------------------
            | CAMBIO DE FECHA
            |--------------------------------------------------------------------------
            */

            dateInput.addEventListener(
                'change',
                updateRecurrenceOptions
            );


            /*
            |--------------------------------------------------------------------------
            | RESTAURAR VALOR ANTERIOR
            |--------------------------------------------------------------------------
            */

            if (recurrenceType.value === 'weekly') {

                weeklySwitch.checked = true;

                monthlySwitch.checked = false;

            }

            if (recurrenceType.value === 'monthly') {

                monthlySwitch.checked = true;

                weeklySwitch.checked = false;

            }


            /*
            |--------------------------------------------------------------------------
            | INICIALIZAR
            |--------------------------------------------------------------------------
            */

            updateRecurrenceOptions();

        });
    </script>
@endsection
