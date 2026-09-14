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


            <div class="row g-3">

                @foreach ($accounts as $account)
                    <div class="col-12 col-sm-6 col-xl-3">

                        <div class="account-card h-100">


                            <div class="w-100">

                                <div class="account-top">

                                    @if ($account->logo)
                                        <img class="account-logo" src="{{ Storage::url($account->logo) }}"
                                            alt="{{ $account->name }}">
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


                                <div class="mt-4">

                                    <input type="text" class="dark-input w-100 money-input"
                                        name="balances[{{ $account->id }}]" placeholder="0" inputmode="decimal"
                                        autocomplete="off" required>

                                </div>

                            </div>


                        </div>

                    </div>
                @endforeach

            </div>



            <div class="opening-day-actions mt-4 d-flex justify-content-center">

                <button type="submit" class="primary-action-button">

                    <i class="bi bi-play-circle"></i>

                    Abrir jornada

                </button>

            </div>


        </form>


    </div>
@endsection
