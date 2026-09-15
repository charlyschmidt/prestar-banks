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
                'in:operator,administration'
            ]

        ]);


        User::create([

            'name' => $data['username'],

            'username' => $data['username'],

            'email' => $data['username'] . '@local',

            'password' => Hash::make(
                $data['password']
            ),

            'role' => $data['role'],

            'is_admin' => false

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
        $rules = [

            'password' => [
                'nullable',
                'string',
                'min:8'
            ]

        ];


        /*
        |--------------------------------------------------------------------------
        | Solo usuarios normales pueden cambiar de rol
        |--------------------------------------------------------------------------
        |
        | El Super Admin nunca modifica su role desde este formulario.
        |
        */

        if (!$usuario->is_admin) {

            $rules['role'] = [
                'required',
                'in:operator,administration'
            ];
        }


        $data = $request->validate(
            $rules,
            [
                'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',

                'role.required' =>
                'Debés seleccionar un rol.',

                'role.in' =>
                'El rol seleccionado no es válido.',
            ]
        );


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
        | Rol
        |--------------------------------------------------------------------------
        */

        if (!$usuario->is_admin) {

            $updateData['role'] = $data['role'];
        }


        if (!empty($updateData)) {

            $usuario->update(
                $updateData
            );
        }


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario actualizado correctamente'
            );
    }


    public function destroy(User $usuario)
    {
        if ($usuario->is_admin) {

            return redirect()
                ->route('usuarios.index')
                ->with(
                    'error',
                    'El Super Admin no puede eliminarse.'
                );
        }


        $usuario->delete();


        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario eliminado correctamente'
            );
    }
}
