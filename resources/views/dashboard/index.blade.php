@extends('layouts.app')


@section('content')

<div class="page-container dashboard">





    {{-- HEADER --}}


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">


        <div>


            <h1>
                Jornada actual
            </h1>


            <p id="last-update" class="mt-2">


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



                @else


                    · Sin movimientos todavía


                @endif


            </p>


        </div>


    </div>









    {{-- BANCOS --}}



    <div class="section-title mb-4">


        <h1>
            Mis bancos
        </h1>


    </div>








    <div class="row g-3">



        @foreach ($summary['accounts'] as $account)



            <div class="col-12 col-sm-6 col-xl-3">


                <a href="{{ route('accounts.movements', $account['id']) }}"
                   class="text-decoration-none">



                    <div class="bank-card h-100"
                         data-account-id="{{ $account['id'] }}">






                        <div class="bank-header">



                            @if ($account['logo'])


                                <img src="{{ $account['logo'] }}"
                                     alt="{{ $account['name'] }}">


                            @endif





                            <div>


                                <h4>
                                    {{ $account['name'] }}
                                </h4>


                                <small>
                                    Cuenta bancaria
                                </small>


                            </div>



                        </div>








                        <div class="bank-balance"
                             data-balance>


                            ${{ number_format($account['balance'], 0, ',', '.') }}


                        </div>








                        <div class="bank-initial">


                            Inicial:

                            ${{ number_format($account['initial_balance'], 0, ',', '.') }}


                        </div>








                        <div class="bank-footer mt-2">



                            <span class="income"
                                  data-income>


                                <i class="bi bi-arrow-up"></i>


                                ${{ number_format($account['income'], 0, ',', '.') }}



                            </span>






                            <span class="expense"
                                  data-expense>


                                <i class="bi bi-arrow-down"></i>


                                ${{ number_format($account['expense'], 0, ',', '.') }}



                            </span>






                            <span class="bank-movements"
                                  data-movements
                                  title="Movimientos de la jornada">


                                <i class="bi bi-arrow-left-right"></i>


                                {{ $account['movements'] ?? 0 }}



                            </span>



                        </div>




                    </div>




                </a>



            </div>



        @endforeach



    </div>









    {{-- MOVIMIENTOS --}}



    <div class="movements mt-4">



        <div class="section-title">


            <h3>
                Movimientos de la jornada
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
                            Banco
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


                    @foreach ($summary['movements'] as $movement)


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


                                <strong>

                                    {{ $movement->description ?? 'Sin descripción' }}

                                </strong>


                            </td>






                            <td>

                                {{ $movement->account->name }}


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


                                    <span class="amount-income">

                                        +

                                        ${{ number_format($movement->amount, 0, ',', '.') }}


                                    </span>


                                @else


                                    <span class="amount-expense">

                                        -

                                        ${{ number_format($movement->amount, 0, ',', '.') }}


                                    </span>


                                @endif



                            </td>







                            <td>


                                ${{ number_format($movement->initial_balance ?? 0, 0, ',', '.') }}


                            </td>







                            <td>


                                ${{ number_format($movement->balance_after ?? 0, 0, ',', '.') }}


                            </td>





                        </tr>


                    @endforeach



                </tbody>



            </table>



        </div>



    </div>





</div>


@endsection