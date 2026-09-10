@extends('layouts.app')


@section('content')
    <div class="page-container">



        <div class="page-header">


            <div>

                <h1>
                    Movimientos
                </h1>


                <p>
                    Jornada actual
                </p>

            </div>



            <a href="{{ route('transactions.create') }}" class="action-button">

                <i class="bi bi-plus"></i>

                Nuevo

            </a>



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
                                Tipo
                            </th>


                            <th>
                                Descripción
                            </th>


                            <th class="text-end">
                                Monto
                            </th>


                            <th></th>


                        </tr>


                    </thead>






                    <tbody id="transactions-body">



                        @foreach ($transactions as $transaction)
                            <tr>


                                <td>

                                    {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}

                                </td>





                                <td>


                                    <div class="account-cell">


                                        @if ($transaction->account->logo)
                                            <img src="{{ Storage::url($transaction->account->logo) }}">
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







                                <td>


                                    {{ $transaction->description ?? 'Sin descripción' }}


                                </td>








                                <td class="text-end">


                                    @if (in_array($transaction->type, ['income', 'transfer_in']))
                                        <span class="amount-income">

                                            +
                                            ${{ number_format($transaction->amount, 2, ',', '.') }}

                                        </span>
                                    @else
                                        <span class="amount-expense">

                                            -
                                            ${{ number_format($transaction->amount, 2, ',', '.') }}


                                        </span>
                                    @endif



                                </td>








                                <td>


                                    <div class="table-actions">


                                        <a href="{{ route('transactions.edit', $transaction) }}" class="icon-button">

                                            <i class="bi bi-pencil"></i>

                                        </a>





                                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}">


                                            @csrf
                                            @method('DELETE')


                                            <button class="icon-button danger">

                                                <i class="bi bi-trash"></i>

                                            </button>


                                        </form>



                                    </div>



                                </td>



                            </tr>
                        @endforeach



                    </tbody>




                </table>



            </div>



        </div>




    </div>
@endsection
