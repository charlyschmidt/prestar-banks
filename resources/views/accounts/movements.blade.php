@extends('layouts.app')


@section('content')
    <div class="page-container">



        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">





            <div class="d-flex align-items-center gap-3 flex-wrap">



                <a href="{{ route('dashboard') }}" class="secondary-button back-button">


                    <i class="bi bi-arrow-left"></i>

                    Volver


                </a>





                @if ($account->logo)
                    <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}"
                        style="
                        height:45px;
                        width:45px;
                        object-fit:contain;
                    ">
                @endif






                <div>


                    <h1 class="mb-1">

                        {{ $account->name }}

                    </h1>



                    <p class="mb-0">

                        Movimientos de la jornada actual

                    </p>


                </div>



            </div>






            <div>


                <a href="{{ route('accounts.export', $account->id) }}" class="primary-action-button">


                    <i class="bi bi-file-earmark-arrow-down"></i>

                    Exportar a Excel


                </a>


            </div>



        </div>








        {{-- RESUMEN CUENTA --}}



        <div class="row g-3">



            <div class="col-12 col-md-6">


                <div class="account-card h-100">


                    <div>


                        <span>
                            Saldo inicial
                        </span>


                        <h3>

                            ${{ number_format($balance->initial_balance ?? 0, 0, ',', '.') }}

                        </h3>


                    </div>


                </div>


            </div>






            <div class="col-12 col-md-6">


                <div class="account-card h-100">


                    <div>


                        <span>
                            Saldo actual
                        </span>


                        <h3 class="green">


                            ${{ number_format($balance->current_balance ?? 0, 0, ',', '.') }}


                        </h3>


                    </div>


                </div>


            </div>



        </div>









        {{-- MOVIMIENTOS --}}



        <div class="movements mt-4">



            <div class="section-title">

                <h3>
                    Movimientos
                </h3>

            </div>





            <div class="table-responsive">

                <table class="modern-table">

                    <thead>

                        <tr>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Concepto
                            </th>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Monto
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($movements as $movement)
                            <tr>

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


                                <td>
                                    {{ $movement->description }}
                                </td>


                                <td>

                                    <div class="movement-user">

                                        <i class="bi bi-person-circle"></i>

                                        <span>
                                            {{ $movement->user?->name ?? 'Sin registro' }}
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    @if (in_array($movement->type, ['income', 'transfer_in']))
                                        <span class="tag income-tag">

                                            <i class="bi bi-arrow-up"></i>

                                            Ingreso

                                        </span>
                                    @else
                                        <span class="tag expense-tag">

                                            <i class="bi bi-arrow-down"></i>

                                            Egreso

                                        </span>
                                    @endif

                                </td>


                                <td class="amount">

                                    @if (in_array($movement->type, ['income', 'transfer_in']))
                                        <span class="green">

                                            +
                                        @else
                                            <span class="red">

                                                -
                                    @endif

                                    ${{ number_format($movement->amount, 2, ',', '.') }}

                                    </span>

                                </td>

                                <td>

                                    <div class="table-actions">

                                        @if (auth()->user()->is_admin || $movement->user_id === auth()->id())
                                            <a href="{{ route('transactions.edit', $movement) }}" class="icon-button"
                                                title="Editar movimiento">
                                                <i class="bi bi-pencil"></i>
                                            </a>


                                            <form method="POST" action="{{ route('transactions.destroy', $movement) }}"
                                                onsubmit="return confirm('¿Seguro que querés eliminar este movimiento?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="icon-button danger"
                                                    title="Eliminar movimiento">
                                                    <i class="bi bi-trash"></i>
                                                </button>

                                            </form>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6">
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
