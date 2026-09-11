@extends('layouts.app')

@section('content')
    <div class="dashboard">


        {{-- HEADER --}}

        <div class="dashboard-header">

            <div>

                <h1>
                    Usuarios
                </h1>


                <p class="mt-2">

                    Administración de accesos al sistema

                </p>

            </div>



            <div class="header-action">

                <a href="{{ route('usuarios.create') }}" class="primary-action-button">

                    <i class="bi bi-person-plus"></i>

                    Nuevo usuario

                </a>

            </div>


        </div>





        {{-- LISTADO --}}

        <div class="section-title mb-4">

            <h2>
                Usuarios registrados
            </h2>

        </div>





       <div class="movements users-table">


            <table>


                <thead>

                    <tr>

                        <th>
                            Usuario
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


                    @foreach ($users as $user)
                        <tr>


                            <td>

                                <strong>

                                    {{ $user->username }}

                                </strong>


                            </td>




                            <td>


                                @if ($user->is_admin)
                                    <span class="tag income-tag">

                                        <i class="bi bi-shield-check"></i>

                                        Administrador

                                    </span>
                                @else
                                    <span class="tag">

                                        <i class="bi bi-person"></i>

                                        Usuario

                                    </span>
                                @endif


                            </td>




                            <td>

                                {{ $user->created_at?->format('d/m/Y') }}


                            </td>




                            <td class="actions-cell">


                                <div class="table-actions">


                                    <a href="{{ route('usuarios.edit', $user->id) }}" class="action-button">

                                        <i class="bi bi-key"></i>

                                    </a>



                                    @if ($user->username !== 'ivantorio')
                                        <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST">

                                            @csrf

                                            @method('DELETE')


                                            <button type="submit" class="action-button danger"
                                                onclick="return confirm('¿Eliminar usuario?')">

                                                <i class="bi bi-trash"></i>

                                            </button>


                                        </form>
                                    @endif


                                </div>


                            </td>



                        </tr>
                    @endforeach


                </tbody>



            </table>


        </div>


    </div>
@endsection
