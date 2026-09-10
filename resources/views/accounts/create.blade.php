@extends('layouts.app')


@section('content')


<div class="page-container">



    <div class="page-header">


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







            <div class="form-actions">


                <a href="{{ route('accounts.index') }}"
                class="secondary-button">

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