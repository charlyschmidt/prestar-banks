@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Cuentas
            </h1>

            <p>
                Administrá tus bancos, billeteras y monedas
            </p>

        </div>


        <a
            href="{{ route('accounts.create') }}"
            class="primary-action-button"
        >

            <i class="bi bi-plus-lg"></i>

            Nueva cuenta

        </a>

    </div>



    <div class="row g-3">


        @forelse ($accounts as $account)


            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">


                <div class="account-card h-100">


                    {{-- CONTENIDO --}}

                    <div class="account-card-content">


                        {{-- CUENTA --}}

                        <div class="account-top">


                            @if ($account->logo)

                                <img
                                    src="{{ Storage::url($account->logo) }}"
                                    class="account-logo"
                                    alt="{{ $account->name }}"
                                >

                            @else

                                <div class="account-logo empty">

                                    <i class="bi bi-bank"></i>

                                </div>

                            @endif


                            <div class="account-info">

                                <h3>
                                    {{ $account->name }}
                                </h3>


                                <span>

                                    @switch($account->type)

                                        @case('bank')
                                            Banco
                                            @break

                                        @case('wallet')
                                            Billetera virtual
                                            @break

                                        @case('cash')
                                            Efectivo
                                            @break

                                        @default
                                            {{ ucfirst($account->type) }}

                                    @endswitch

                                </span>

                            </div>


                        </div>



                        {{-- MONEDAS --}}

                        <div class="account-currencies">


                            <span class="account-currencies-label">
                                Monedas
                            </span>


                            <div class="account-currency-list">


                                @forelse (
                                    $account->balances->sortBy('currency')
                                    as $balance
                                )

                                    <span class="account-currency-badge">

                                        {{ $balance->currency }}

                                    </span>

                                @empty

                                    <span class="account-currency-empty">

                                        Sin monedas configuradas

                                    </span>

                                @endforelse


                            </div>


                        </div>


                    </div>



                    {{-- FOOTER / ACCIONES --}}

                    <div class="account-card-footer">


                        <a
                            href="{{ route('accounts.edit', $account) }}"
                            class="account-card-action"
                            title="Editar cuenta"
                            aria-label="Editar cuenta"
                        >

                            <i class="bi bi-pencil"></i>

                        </a>


                        <form
                            method="POST"
                            action="{{ route('accounts.destroy', $account) }}"
                            class="account-delete-form"
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="submit"
                                class="account-card-action danger"
                                title="Eliminar cuenta"
                                aria-label="Eliminar cuenta"
                            >

                                <i class="bi bi-trash"></i>

                            </button>


                        </form>


                    </div>


                </div>


            </div>


        @empty


            <div class="col-12">

                <div class="account-empty-state">

                    <i class="bi bi-bank"></i>

                    <h3>
                        Todavía no hay cuentas
                    </h3>

                    <p>
                        Creá tu primera cuenta para comenzar a operar.
                    </p>

                    <a
                        href="{{ route('accounts.create') }}"
                        class="primary-action-button"
                    >

                        <i class="bi bi-plus-lg"></i>

                        Nueva cuenta

                    </a>

                </div>

            </div>


        @endforelse


    </div>


</div>

@endsection