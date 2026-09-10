@extends('layouts.app')


@section('content')
    <div class="page-container">


        <div class="page-header">


            <div>

                <h1>
                    Cuentas
                </h1>

                <p>
                    Administrá tus bancos y billeteras
                </p>

            </div>



            <a href="{{ route('accounts.create') }}" class="primary-action-button">

                <i class="bi bi-plus-lg"></i>

                Nueva cuenta

            </a>


        </div>






        <div class="accounts-grid">


            @foreach ($accounts as $account)
                <div class="account-card">



                    <div class="account-top">


                        @if ($account->logo)
                            <img src="{{ Storage::url($account->logo) }}" class="account-logo">
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
                                {{ ucfirst($account->type) }}
                            </span>


                        </div>


                    </div>





                    <div class="account-actions">


                        <a href="{{ route('accounts.edit', $account) }}" class="icon-button">

                            <i class="bi bi-pencil"></i>

                        </a>





                        <form method="POST" action="{{ route('accounts.destroy', $account) }}">


                            @csrf
                            @method('DELETE')


                            <button class="icon-button danger">

                                <i class="bi bi-trash"></i>

                            </button>


                        </form>


                    </div>



                </div>
            @endforeach


        </div>



    </div>
@endsection
