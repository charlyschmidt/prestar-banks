@extends('layouts.app')

@section('content')
    <div class="dashboard">


        <div class="dashboard-header">

            <div>

                <h1>
                    Editar usuario
                </h1>

                <p class="mt-2">
                    Cambiar acceso de {{ $user->username }}
                </p>

            </div>

        </div>




        <div class="movements users-table">

            @if ($errors->any())
                <div class="alert-error mb-4">

                    <i class="bi bi-exclamation-triangle"></i>

                    {{ $errors->first() }}

                </div>
            @endif

            <form method="POST" action="{{ route('usuarios.update', $user->id) }}">


                @csrf

                @method('PUT')



                <div class="mb-4">


                    <label class="login-label">
                        Usuario
                    </label>


                    <input type="text" class="login-input" value="{{ $user->username }}" readonly>


                </div>




                <div class="mb-4">


                    <label class="login-label">
                        Nueva contraseña
                    </label>


                    <input type="password" name="password" class="login-input" required>


                </div>




                <button class="primary-action-button">


                    <i class="bi bi-key"></i>

                    Cambiar contraseña


                </button>



            </form>



        </div>


    </div>
@endsection
