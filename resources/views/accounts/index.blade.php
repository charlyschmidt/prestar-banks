@extends('layouts.app')


@section('content')

<div class="page-container">



    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">


        <div>

            <h1>
                Cuentas
            </h1>


            <p>
                Administrá tus bancos y billeteras
            </p>


        </div>





        <a href="{{ route('accounts.create') }}"
           class="primary-action-button">


            <i class="bi bi-plus-lg"></i>

            Nueva cuenta


        </a>



    </div>








    <div class="row g-3">



        @foreach ($accounts as $account)


            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">



                <div class="account-card h-100">





                    <div class="account-top">



                        @if ($account->logo)

                            <img
                                src="{{ Storage::url($account->logo) }}"
                                class="account-logo">

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









                    <div class="account-actions mt-auto">



                        <a href="{{ route('accounts.edit', $account) }}"
                           class="icon-button">


                            <i class="bi bi-pencil"></i>


                        </a>








                        <form method="POST"
                              action="{{ route('accounts.destroy', $account) }}">


                            @csrf
                            @method('DELETE')



                            <button class="icon-button danger">


                                <i class="bi bi-trash"></i>


                            </button>



                        </form>



                    </div>





                </div>


            </div>



        @endforeach



    </div>




</div>


@endsection