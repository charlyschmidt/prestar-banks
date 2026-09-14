@extends('layouts.app')


@section('content')

<div class="page-container">



    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">


        <div>


            <h1>
                Editar usuario
            </h1>


            <p class="mt-2">
                Cambiar acceso de {{ $user->username }}
            </p>


        </div>


    </div>







    <div class="form-card">





        @if ($errors->any())


            <div class="alert-error mb-4">


                <i class="bi bi-exclamation-triangle"></i>


                {{ $errors->first() }}


            </div>


        @endif







        <form method="POST"
              action="{{ route('usuarios.update', $user->id) }}">



            @csrf

            @method('PUT')







            <div class="form-group mb-4">



                <label class="login-label">

                    Usuario

                </label>




                <input
                    type="text"
                    class="login-input"
                    value="{{ $user->username }}"
                    readonly>



            </div>









            <div class="form-group mb-4">



                <label class="login-label">

                    Nueva contraseña

                </label>




                <input
                    type="password"
                    name="password"
                    class="login-input"
                    required>



            </div>









            <div class="d-flex flex-column flex-md-row justify-content-end">


                <button class="primary-action-button">


                    <i class="bi bi-key"></i>


                    Cambiar contraseña



                </button>


            </div>





        </form>



    </div>





</div>


@endsection