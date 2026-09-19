@extends('layouts.app')


@section('content')

<div class="page-container">


    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Editar usuario
            </h1>

            <p class="mt-2">
                Modificar acceso de {{ $user->name }}
            </p>

        </div>

    </div>



    <div class="form-card">


        @if (session('error'))

            <div class="alert-error mb-4">

                <i class="bi bi-exclamation-triangle"></i>

                {{ session('error') }}

            </div>

        @endif


        @if ($errors->any())

            <div class="alert-error mb-4">

                <i class="bi bi-exclamation-triangle"></i>

                {{ $errors->first() }}

            </div>

        @endif



        <form
            method="POST"
            action="{{ route('usuarios.update', $user->id) }}"
        >

            @csrf

            @method('PUT')



            {{-- NOMBRE --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Nombre
                </label>

                <input
                    type="text"
                    name="name"
                    class="login-input"
                    value="{{ old('name', $user->name) }}"
                    required
                    autocomplete="name"
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
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="email"
                >

                @error('email')

                    <div class="alert-error mt-2">
                        {{ $message }}
                    </div>

                @enderror

            </div>



            {{-- ROL --}}

            @php

                $currentRole =
                    $membership->pivot->is_admin
                        ? 'super_admin'
                        : $membership->pivot->role;

            @endphp


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
                        {{ old('role', $currentRole) === 'operator'
                            ? 'selected'
                            : '' }}
                    >
                        Operador
                    </option>


                    <option
                        value="administration"
                        {{ old('role', $currentRole) === 'administration'
                            ? 'selected'
                            : '' }}
                    >
                        Administración
                    </option>


                    @if (auth()->user()->isSuperAdmin())

                        <option
                            value="super_admin"
                            {{ old('role', $currentRole) === 'super_admin'
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



            {{-- PASSWORD --}}

            <div class="form-group mb-4">

                <label class="login-label">
                    Nueva contraseña
                </label>

                <input
                    type="password"
                    name="password"
                    class="login-input"
                    autocomplete="new-password"
                >

                <small class="form-text text-white-50">
                    Dejá este campo vacío si no querés cambiar la contraseña.
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

                    <i class="bi bi-check-lg"></i>

                    Guardar cambios

                </button>

            </div>


        </form>

    </div>


</div>

@endsection