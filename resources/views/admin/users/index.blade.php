@extends('layouts.app')


@section('content')

<div class="page-container">


    {{-- HEADER --}}

    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <div>

            <h1>
                Usuarios
            </h1>

            <p class="mt-2">
                Administración de accesos al sistema
            </p>

        </div>


        <div>

            <a
                href="{{ route('usuarios.create') }}"
                class="primary-action-button"
            >
                <i class="bi bi-person-plus"></i>

                Nuevo usuario
            </a>

        </div>

    </div>



    {{-- MENSAJES --}}

    @if (session('success'))

        <div class="alert-success mb-4">

            <i class="bi bi-check-circle"></i>

            {{ session('success') }}

        </div>

    @endif


    @if (session('error'))

        <div class="alert-error mb-4">

            <i class="bi bi-exclamation-triangle"></i>

            {{ session('error') }}

        </div>

    @endif



    {{-- LISTADO --}}

    <div class="section-title mb-4">

        <h2>
            Usuarios registrados
        </h2>

    </div>


    <div class="movements users-table">

        <div class="table-responsive">

            <table class="modern-table">

                <thead>

                    <tr>

                        <th>
                            Usuario
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Tipo
                        </th>

                        <th>
                            Creado
                        </th>

                        <th class="text-end">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse ($users as $user)

                        @php

                            /*
                             * El controlador carga solamente
                             * la membresía de la empresa activa.
                             */

                            $membership =
                                $user->companies->first();

                            $isSuperAdmin =
                                (bool) optional(
                                    $membership?->pivot
                                )->is_admin;

                            $role =
                                optional(
                                    $membership?->pivot
                                )->role;

                        @endphp


                        <tr>

                            {{-- NOMBRE --}}

                            <td>

                                <strong>
                                    {{ $user->name }}
                                </strong>

                            </td>


                            {{-- EMAIL --}}

                            <td>

                                {{ $user->email }}

                            </td>


                            {{-- ROL EN ESTA EMPRESA --}}

                            <td>

                                @if ($isSuperAdmin)

                                    <span class="tag income-tag">

                                        <i class="bi bi-shield-check"></i>

                                        Super Admin

                                    </span>

                                @elseif ($role === 'administration')

                                    <span class="tag administration-tag">

                                        <i class="bi bi-briefcase"></i>

                                        Administración

                                    </span>

                                @else

                                    <span class="tag">

                                        <i class="bi bi-person"></i>

                                        Operador

                                    </span>

                                @endif

                            </td>


                            {{-- FECHA --}}

                            <td>

                                {{ $user->created_at?->format('d/m/Y') }}

                            </td>


                            {{-- ACCIONES --}}

                            <td class="actions-cell">

                                <div class="table-actions justify-content-end">


                                    <a
                                        href="{{ route('usuarios.edit', $user->id) }}"
                                        class="action-button"
                                        title="Editar usuario"
                                    >

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    @if ($user->id !== auth()->id())

                                        <form
                                            action="{{ route('usuarios.destroy', $user->id) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="action-button danger"
                                                title="Quitar usuario"
                                                onclick="return confirm('¿Quitar este usuario de la empresa?')"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="text-center"
                            >
                                No hay usuarios registrados.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection