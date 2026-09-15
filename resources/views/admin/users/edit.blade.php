@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Editar usuario
            </h1>

            <p class="mt-2">
                Modificar acceso de {{ $user->username }}
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



            @if (!$user->is_admin)

                <div class="form-group mb-4">

                    <label class="login-label">
                        Rol
                    </label>

                    <select
                        name="role"
                        class="login-input"
                        required
                    >

                        <option
                            value="operator"
                            {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}
                        >
                            Operador
                        </option>

                        <option
                            value="administration"
                            {{ old('role', $user->role) === 'administration' ? 'selected' : '' }}
                        >
                            Administración
                        </option>

                    </select>

                    @error('role')

                        <div class="alert-error mt-2">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            @else

                <div class="form-group mb-4">

                    <label class="login-label">
                        Tipo de usuario
                    </label>

                    <input
                        type="text"
                        class="login-input"
                        value="Super Admin"
                        readonly>

                </div>

            @endif



            <div class="form-group mb-4">

                <label class="login-label">
                    Nueva contraseña
                </label>

                <input
                    type="password"
                    name="password"
                    class="login-input"
                    autocomplete="new-password">

                <small class="form-text text-white-50">
                    Dejá este campo vacío si no querés cambiar la contraseña.
                </small>

                @error('password')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            <div class="d-flex flex-column flex-md-row justify-content-end">

                <button
                    type="submit"
                    class="primary-action-button"
                >

                    <i class="bi bi-check-lg"></i>

                    Guardar cambios

                </button>

            </div>


        </form>

    </div>


</div>

@endsection