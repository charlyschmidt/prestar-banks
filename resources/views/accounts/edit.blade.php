@extends('layouts.app')


@section('content')


<div class="page-container">



    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">


        <div>

            <h1>
                Editar cuenta
            </h1>


            <p>
                Modificá los datos del banco, billetera o efectivo
            </p>


        </div>


    </div>







    <div class="form-card">



        <form method="POST"
              action="{{ route('accounts.update', $account->id) }}"
              enctype="multipart/form-data">


            @csrf

            @method('PUT')






            <div class="row g-3">



                <div class="col-12">


                    <div class="form-group">


                        <label>
                            Nombre de la cuenta
                        </label>


                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $account->name) }}"
                            placeholder="Ej: Banco Galicia"
                            class="dark-input"
                            required>


                    </div>


                </div>






                <div class="col-12 col-md-6">


                    <div class="form-group">


                        <label>
                            Tipo de cuenta
                        </label>


                        <select
                            name="type"
                            class="dark-input">



                            <option value="bank"
                            {{ $account->type == 'bank' ? 'selected' : '' }}>
                                Banco
                            </option>



                            <option value="wallet"
                            {{ $account->type == 'wallet' ? 'selected' : '' }}>
                                Billetera virtual
                            </option>



                            <option value="cash"
                            {{ $account->type == 'cash' ? 'selected' : '' }}>
                                Efectivo
                            </option>


                        </select>


                    </div>


                </div>







                <div class="col-12 col-md-6">


                    <div class="form-group">


                        <label>
                            Logo
                        </label>



                        <input
                            type="file"
                            name="logo"
                            class="dark-input">





                        @if($account->logo)

                            <div class="mt-3">


                                <p class="small text-muted mb-2">
                                    Logo actual
                                </p>



                                <img
                                    src="{{ asset('storage/'.$account->logo) }}"
                                    style="
                                        width:70px;
                                        height:70px;
                                        object-fit:contain;
                                        background:white;
                                        border-radius:12px;
                                        padding:8px;
                                    ">


                            </div>


                        @endif





                        <small>
                            PNG o JPG recomendado
                        </small>


                    </div>


                </div>



            </div>









            <div class="form-actions d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">



                <a href="{{ route('accounts.index') }}"
                   class="secondary-button text-center">


                    Cancelar


                </a>






                <button class="primary-action-button">


                    <i class="bi bi-check-lg"></i>

                    Guardar cambios


                </button>



            </div>





        </form>


    </div>





</div>


@endsection