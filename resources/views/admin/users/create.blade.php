@extends('layouts.app')

@section('content')
    <div class="dashboard">


        <div class="dashboard-header">

            <div>

                <h1>
                    Nuevo usuario
                </h1>

                <p class="mt-2">
                    Crear un nuevo acceso al sistema
                </p>

            </div>

        </div>



        <div class="form-card">


            <form method="POST" action="{{ route('usuarios.store') }}">

                @csrf


                <div class="mb-4">

                    <label class="login-label">
                        Usuario
                    </label>


                    <input type="text" name="username" class="login-input" value="{{ old('username') }}" required>


                    @error('username')
                        <div class="alert-error mt-2">
                            {{ $message }}
                        </div>
                    @enderror


                </div>



                <div class="mb-4">


                    <label class="login-label">
                        Contraseña
                    </label>


                    <input type="password" name="password" class="login-input" required>


                    @error('password')
                        <div class="alert-error mt-2">
                            {{ $message }}
                        </div>
                    @enderror


                </div>




                <button class="primary-action-button">

                    <i class="bi bi-person-plus"></i>

                    Crear usuario

                </button>


            </form>


        </div>


    </div>
@endsection
