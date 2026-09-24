<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Services\AeriaMailService;

class RegisteredUserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Formulario de registro
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('auth.register');
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar empresa
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        AeriaMailService $mailService
    ) {
        $data = $request->validate(
            [
                'company_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8),
                ],
            ],
            [
                'company_name.required' =>
                'Ingresá el nombre de la empresa.',

                'name.required' =>
                'Ingresá tu nombre.',

                'email.required' =>
                'Ingresá tu email.',

                'email.email' =>
                'Ingresá un email válido.',

                'email.unique' =>
                'Ya existe una cuenta registrada con ese email.',

                'password.required' =>
                'Ingresá una contraseña.',

                'password.confirmed' =>
                'Las contraseñas no coinciden.',
            ]
        );


        $user = DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Crear empresa pendiente
            |--------------------------------------------------------------------------
            */

            $company = Company::create([
                'name' => $data['company_name'],

                'slug' => $this->generateCompanySlug(
                    $data['company_name']
                ),

                'email' => $data['email'],

                'status' => 'pending',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Crear primer usuario
            |--------------------------------------------------------------------------
            |
            | Sigue existiendo username temporalmente en users,
            | así que lo completamos hasta eliminar esa columna legacy.
            |
            */

            $user = User::create([
                'name' => $data['name'],

                'username' => $this->generateUsername(
                    $data['email']
                ),

                'email' => $data['email'],

                'password' => Hash::make(
                    $data['password']
                ),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Convertirlo en Super Admin de su empresa
            |--------------------------------------------------------------------------
            */

            $user->companies()->attach(
                $company->id,
                [
                    'role' => 'operator',
                    'is_admin' => true,
                ]
            );
            return $user;
        });

        $mailService->sendAccountCreated(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | No iniciar sesión
        |--------------------------------------------------------------------------
        |
        | La empresa todavía está pendiente de aprobación.
        |
        */

        return redirect()
            ->route('register.pending');
    }


    /*
    |--------------------------------------------------------------------------
    | Slug único para empresa
    |--------------------------------------------------------------------------
    */

    private function generateCompanySlug(
        string $companyName
    ): string {

        $base = Str::slug($companyName);

        if (!$base) {
            $base = 'empresa';
        }


        $slug = $base;
        $counter = 1;


        while (
            Company::where('slug', $slug)
            ->exists()
        ) {

            $slug =
                $base
                . '-'
                . $counter;

            $counter++;
        }


        return $slug;
    }


    /*
    |--------------------------------------------------------------------------
    | Username temporal
    |--------------------------------------------------------------------------
    */

    private function generateUsername(
        string $email
    ): string {

        $base = strtolower(
            explode('@', $email)[0]
        );


        $base = preg_replace(
            '/[^a-z0-9._-]/',
            '',
            $base
        );


        if (!$base) {
            $base = 'usuario';
        }


        $username = $base;
        $counter = 1;


        while (
            User::withTrashed()
            ->where(
                'username',
                $username
            )
            ->exists()
        ) {

            $username =
                $base
                . $counter;

            $counter++;
        }


        return $username;
    }
}
