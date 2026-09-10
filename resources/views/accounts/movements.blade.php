@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">


            <div class="d-flex align-items-center gap-3">

                @if ($account->logo)
                    <img src="{{ asset('storage/' . $account->logo) }}" alt="{{ $account->name }}"
                        style="height:45px;object-fit:contain;">
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



            <a href="{{ route('dashboard') }}" class="secondary-button">

                <i class="bi bi-arrow-left"></i>

                Volver

            </a>


        </div>





        {{-- RESUMEN CUENTA --}}


        <div class="accounts-grid">


            <div class="account-card">


                <div>

                    <span>
                        Saldo inicial
                    </span>


                    <h3>

                        ${{ number_format($balance->initial_balance ?? 0, 0, ',', '.') }}

                    </h3>

                </div>


            </div>



            <div class="account-card">


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







        {{-- MOVIMIENTOS --}}


        <div class="movements">


            <div class="section-title">

                <h3>

                    Movimientos

                </h3>

            </div>




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
                            Tipo
                        </th>


                        <th>
                            Monto
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



                                ${{ number_format($movement->amount, 0, ',', '.') }}


                                </span>


                            </td>



                        </tr>



                    @empty


                        <tr>

                            <td colspan="4">

                                Sin movimientos en esta jornada

                            </td>


                        </tr>
                    @endforelse



                </tbody>


            </table>


        </div>



    </div>
@endsection
