@extends('layouts.app')


@section('content')


<div class="page-container">



    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">


        <div>

            <h1>
                Nueva cuenta
            </h1>


            <p>
                Agregá un banco, billetera o efectivo
            </p>


        </div>



    </div>







    <div class="form-card">



        <form method="POST"
              action="{{ route('accounts.store') }}"
              enctype="multipart/form-data">


            @csrf



            <div class="row g-3">



                <div class="col-12">


                    <div class="form-group">


                        <label>
                            Nombre de la cuenta
                        </label>


                        <input
                            type="text"
                            name="name"
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


                            <option value="bank">
                                Banco
                            </option>


                            <option value="wallet">
                                Billetera virtual
                            </option>


                            <option value="cash">
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





                <button class="primary-button">


                    <i class="bi bi-check-lg"></i>

                    Guardar cuenta


                </button>



            </div>




        </form>


    </div>




</div>


@endsection