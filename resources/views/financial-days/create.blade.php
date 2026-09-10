@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header">

        <div>

            <h1>
                Apertura de jornada
            </h1>


            <p>
                Cargá los saldos iniciales de tus cuentas para comenzar el día.
            </p>

        </div>


    </div>




    <form method="POST" action="{{ route('financial-days.store') }}">

        @csrf



        <div class="accounts-grid">


            @foreach ($accounts as $account)


                <div class="account-card">


                    <div>


                        <div class="account-top">


                            @if($account->logo)

                                <img 
                                    class="account-logo"
                                    src="{{ Storage::url($account->logo) }}"
                                >

                            @else

                                <div class="account-logo empty">

                                    <i class="bi bi-bank"></i>

                                </div>

                            @endif



                            <div>

                                <h3>
                                    {{ $account->name }}
                                </h3>


                                <span>
                                    Saldo inicial
                                </span>

                            </div>


                        </div>



                        <div style="margin-top:20px">


                            <input

                                type="number"

                                step="0.01"

                                class="dark-input"

                                name="balances[{{ $account->id }}]"

                                placeholder="0.00"

                                required

                            >


                        </div>


                    </div>


                </div>


            @endforeach


        </div>




        <div class="form-actions">


            <button class="primary-action-button">


                <i class="bi bi-play-circle"></i>

                Abrir jornada


            </button>


        </div>



    </form>



</div>


@endsection