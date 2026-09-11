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
            ]

        ]);



        User::create([

            'name' => $data['username'],

            'username' => $data['username'],

            'email' => $data['username'] . '@local',

            'password' => Hash::make(
                $data['password']
            ),

            'is_admin' => false

        ]);



        return redirect()
            ->route('usuarios.index');
    }




    public function edit(User $usuario)
    {
        return view('admin.users.edit', [
            'user' => $usuario
        ]);
    }





    public function update(Request $request, User $usuario)
    {
        $data = $request->validate(
            [
                'password' => [
                    'nullable',
                    'string',
                    'min:8'
                ]
            ],
            [
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            ]
        );


        if (!empty($data['password'])) {

            $usuario->update([
                'password' => Hash::make($data['password'])
            ]);
        }


        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Contraseña actualizada correctamente');
    }





    public function destroy(User $usuario)
    {

        $usuario->delete();


        return redirect()
            ->route('usuarios.index');
    }
}
