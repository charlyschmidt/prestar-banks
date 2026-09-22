@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Nuevo usuario
            </h1>

            <p class="mt-2">
                Agregar un usuario a la empresa
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



        <form
            method="POST"
            action="{{ route('users.store') }}"
        >

            @csrf



            {{-- NOMBRE --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Nombre
                </label>

                <input
                    type="text"
                    name="name"
                    class="login-input"
                    value="{{ old('name') }}"
                    placeholder="Nombre y apellido"
                    autocomplete="name"
                    required
                >

                @error('name')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            {{-- EMAIL --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="login-input"
                    value="{{ old('email') }}"
                    placeholder="usuario@empresa.com"
                    autocomplete="email"
                    required
                >

                @error('email')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            {{-- ROL --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Rol
                </label>

                <select
                    name="role"
                    class="login-input"
                    required
                >

                    <option value="">
                        Seleccionar rol
                    </option>

                    <option
                        value="operator"
                        {{ old('role') === 'operator'
                            ? 'selected'
                            : '' }}
                    >
                        Operador
                    </option>

                    <option
                        value="administration"
                        {{ old('role') === 'administration'
                            ? 'selected'
                            : '' }}
                    >
                        Administración
                    </option>

                    @if (auth()->user()->isSuperAdmin())

                        <option
                            value="super_admin"
                            {{ old('role') === 'super_admin'
                                ? 'selected'
                                : '' }}
                        >
                            Super Admin
                        </option>

                    @endif

                </select>

                @error('role')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            {{-- CONTRASEÑA --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Contraseña
                </label>

                <input
                    type="password"
                    name="password"
                    class="login-input"
                    autocomplete="new-password"
                    required
                >

                <small class="form-text text-white-50">
                    Mínimo 8 caracteres.
                </small>

                @error('password')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            {{-- ACCIONES --}}

            <div class="d-flex flex-column flex-md-row justify-content-end">

                <button
                    type="submit"
                    class="primary-action-button"
                >

                    <i class="bi bi-person-plus"></i>

                    Agregar usuario

                </button>

            </div>


        </form>

    </div>


</div>

@endsection