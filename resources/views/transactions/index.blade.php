@extends('layouts.app')


@section('content')
    <div class="page-container">



        <div class="page-header">


            <div class="d-flex align-items-center gap-3 movement-title">

                <a href="{{ route('dashboard') }}" class="secondary-button back-button">

                    <i class="bi bi-arrow-left"></i>

                    <span>
                        Volver
                    </span>

                </a>



                <div>

                    <h1>
                        Movimientos
                    </h1>


                    <p>
                        Jornada actual
                    </p>

                </div>


            </div>





            <div class="d-flex gap-2 movement-actions">


                <a href="{{ route('transactions.create') }}" class="primary-action-button">


                    <i class="bi bi-plus"></i>

                    <span>
                        Nuevo
                    </span>


                </a>



                <a href="{{ route('transactions.export') }}" class="primary-action-button">


                    <i class="bi bi-file-earmark-arrow-down"></i>

                    <span>
                        Exportar Excel
                    </span>


                </a>


            </div>



        </div>







        <div class="movements-card">

            <div class="table-responsive">

                <table class="modern-table">

                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Cuenta
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Descripción
                            </th>

                            <th class="text-end">
                                Monto
                            </th>

                            <th class="text-end">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody id="transactions-body">

                        @forelse ($transactions as $transaction)
                            <tr>

                                {{-- FECHA --}}
                                <td>

                                    {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y H:i') }}

                                </td>


                                {{-- CUENTA --}}
                                <td>

                                    <div class="account-cell">

                                        @if ($transaction->account->logo)
                                            <img src="{{ Storage::url($transaction->account->logo) }}"
                                                alt="{{ $transaction->account->name }}">
                                        @else
                                            <div class="mini-logo">

                                                <i class="bi bi-bank"></i>

                                            </div>
                                        @endif


                                        <span>

                                            {{ $transaction->account->name }}

                                        </span>

                                    </div>

                                </td>


                                {{-- USUARIO --}}
                                <td>

                                    <div class="movement-user">

                                        <i class="bi bi-person-circle"></i>

                                        <span>
                                            {{ $transaction->user?->name ?? 'Sin registro' }}
                                        </span>

                                    </div>

                                </td>


                                {{-- TIPO --}}
                                <td>

                                    @if (in_array($transaction->type, ['income', 'transfer_in']))
                                        <span class="movement-income">

                                            <i class="bi bi-arrow-up"></i>

                                            Ingreso

                                        </span>
                                    @else
                                        <span class="movement-expense">

                                            <i class="bi bi-arrow-down"></i>

                                            Egreso

                                        </span>
                                    @endif

                                </td>


                                {{-- DESCRIPCIÓN --}}
                                <td>

                                    {{ $transaction->description ?? 'Sin descripción' }}

                                </td>


                                {{-- MONTO --}}
                                <td class="text-end">

                                    @if (in_array($transaction->type, ['income', 'transfer_in']))
                                        <span class="amount-income">

                                            + ${{ number_format($transaction->amount, 2, ',', '.') }}

                                        </span>
                                    @else
                                        <span class="amount-expense">

                                            - ${{ number_format($transaction->amount, 2, ',', '.') }}

                                        </span>
                                    @endif

                                </td>


                                {{-- ACCIONES --}}
                                <td>

                                    <div class="table-actions">

                                        @if (auth()->user()->is_admin || $transaction->user_id === auth()->id())
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="icon-button"
                                                title="Editar movimiento">

                                                <i class="bi bi-pencil"></i>

                                            </a>


                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}"
                                                onsubmit="return confirm('¿Seguro que querés eliminar este movimiento?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="icon-button danger"
                                                    title="Eliminar movimiento">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>
                                        @else
                                            <span class="text-muted">
                                                —
                                            </span>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-center">

                                    Sin movimientos en esta jornada

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>




    </div>
@endsection
