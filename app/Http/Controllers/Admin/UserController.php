<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::orderBy('username')
            ->get();

        return view(
            'admin.users.index',
            compact('users')
        );
    }


    public function create()
    {
        return view(
            'admin.users.create'
        );
    }


    public function store(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $allowedRoles = [
            'operator',
            'administration'
        ];


        /*
    |--------------------------------------------------------------------------
    | Solo un Super Admin puede crear otro Super Admin
    |--------------------------------------------------------------------------
    */

        if (auth()->user()->is_admin) {
            $allowedRoles[] = 'super_admin';
        }


        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:users'
            ],

            'password' => [
                'required',
                'string',
                'min:6'
            ],

            'role' => [
                'required',
                'in:' . implode(',', $allowedRoles)
            ]
        ]);


        /*
    |--------------------------------------------------------------------------
    | Determinar permisos
    |--------------------------------------------------------------------------
    */

        $isSuperAdmin =
            $data['role'] === 'super_admin';


        /*
    |--------------------------------------------------------------------------
    | Crear usuario
    |--------------------------------------------------------------------------
    */

        User::create([
            'name' => $data['username'],

            'username' => $data['username'],

            'email' =>
            $data['username'] . '@local',

            'password' =>
            Hash::make($data['password']),

            /*
        | Super Admin NO es realmente un role.
        | El permiso real sigue siendo is_admin.
        */

            'role' =>
            $isSuperAdmin
                ? 'operator'
                : $data['role'],

            'is_admin' =>
            $isSuperAdmin
        ]);


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario creado correctamente'
            );
    }


    public function edit(User $usuario)
    {
        return view('admin.users.edit', [
            'user' => $usuario
        ]);
    }


    public function update(Request $request, User $usuario)
    {
        $currentUser = auth()->user();


        /*
    |--------------------------------------------------------------------------
    | Roles permitidos
    |--------------------------------------------------------------------------
    |
    | Solo un Super Admin puede asignar el nivel Super Admin.
    |
    */

        $allowedRoles = [
            'operator',
            'administration'
        ];

        if ($currentUser->is_admin) {
            $allowedRoles[] = 'super_admin';
        }


        /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

        $data = $request->validate(
            [
                'password' => [
                    'nullable',
                    'string',
                    'min:8'
                ],

                'role' => [
                    'required',
                    'in:' . implode(',', $allowedRoles)
                ]
            ],
            [
                'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',

                'role.required' =>
                'Debés seleccionar un rol.',

                'role.in' =>
                'El rol seleccionado no es válido.',
            ]
        );


        /*
    |--------------------------------------------------------------------------
    | Nuevo nivel
    |--------------------------------------------------------------------------
    */

        $willBeSuperAdmin =
            $data['role'] === 'super_admin';


        /*
    |--------------------------------------------------------------------------
    | Protección del último Super Admin
    |--------------------------------------------------------------------------
    |
    | Si el usuario actualmente es Super Admin y se intenta bajarlo
    | a otro rol, verificamos que exista otro Super Admin activo.
    |
    */

        if (
            $usuario->is_admin &&
            !$willBeSuperAdmin
        ) {

            $superAdmins = User::where(
                'is_admin',
                true
            )->count();


            if ($superAdmins <= 1) {

                return redirect()
                    ->back()
                    ->withInput()
                    ->with(
                        'error',
                        'No podés quitar el rol al último Super Admin del sistema.'
                    );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Datos a actualizar
    |--------------------------------------------------------------------------
    */

        $updateData = [];


        /*
    |--------------------------------------------------------------------------
    | Contraseña
    |--------------------------------------------------------------------------
    */

        if (!empty($data['password'])) {

            $updateData['password'] = Hash::make(
                $data['password']
            );
        }


        /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

        if ($willBeSuperAdmin) {

            /*
        | Super Admin se controla mediante is_admin.
        | Dejamos operator como role interno.
        */

            $updateData['is_admin'] = true;
            $updateData['role'] = 'operator';
        } else {

            $updateData['is_admin'] = false;
            $updateData['role'] = $data['role'];
        }


        /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

        $usuario->update(
            $updateData
        );


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario actualizado correctamente'
            );
    }


    public function destroy(User $usuario)
    {
        $currentUser = auth()->user();


        /*
    |--------------------------------------------------------------------------
    | No permitir eliminarse a sí mismo
    |--------------------------------------------------------------------------
    */

        if ($usuario->id === $currentUser->id) {

            return redirect()
                ->route('usuarios.index')
                ->with(
                    'error',
                    'No podés eliminar tu propio usuario.'
                );
        }


        /*
    |--------------------------------------------------------------------------
    | Protección de Super Admin
    |--------------------------------------------------------------------------
    |
    | Se puede eliminar un Super Admin solamente si existe
    | al menos otro Super Admin activo.
    |
    */

        if ($usuario->is_admin) {

            $superAdmins = User::where(
                'is_admin',
                true
            )->count();


            if ($superAdmins <= 1) {

                return redirect()
                    ->route('usuarios.index')
                    ->with(
                        'error',
                        'No se puede eliminar el último Super Admin del sistema.'
                    );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Eliminación lógica
    |--------------------------------------------------------------------------
    */

        $usuario->delete();


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario eliminado correctamente'
            );
    }
}
