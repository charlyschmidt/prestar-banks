<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CompanyContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Usuarios de la empresa activa
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $currentUser = auth()->user();

        $this->ensureCanManageUsers(
            $currentUser
        );

        $companyId = app(
            CompanyContextService::class
        )->id();


        $users = User::query()
            ->whereHas(
                'companies',
                function ($query) use ($companyId) {
                    $query->where(
                        'companies.id',
                        $companyId
                    );
                }
            )
            ->with([
                'companies' => function ($query) use ($companyId) {
                    $query->where(
                        'companies.id',
                        $companyId
                    );
                }
            ])
            ->orderBy('name')
            ->get();


        return view(
            'admin.users.index',
            compact('users')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->ensureCanManageUsers(
            auth()->user()
        );

        return view(
            'admin.users.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Guardar
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $this->ensureCanManageUsers(
            $currentUser
        );


        $companyId = app(
            CompanyContextService::class
        )->id();


        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100'
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255'
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8'
                ],

                'role' => [
                    'required',
                    Rule::in([
                        'operator',
                        'administration',
                        'super_admin'
                    ])
                ]
            ],
            [
                'name.required' =>
                'Ingresá el nombre del usuario.',

                'email.required' =>
                'Ingresá el email.',

                'email.email' =>
                'Ingresá un email válido.',

                'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',

                'role.required' =>
                'Debés seleccionar un rol.',

                'role.in' =>
                'El rol seleccionado no es válido.',
            ]
        );


        DB::transaction(
            function () use (
                $data,
                $companyId
            ) {

                /*
                |--------------------------------------------------------------------------
                | Buscar usuario global por email
                |--------------------------------------------------------------------------
                */

                $user = User::withTrashed()
                    ->where(
                        'email',
                        $data['email']
                    )
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Usuario nuevo
                |--------------------------------------------------------------------------
                */

                if (!$user) {

                    if (empty($data['password'])) {
                        abort(
                            422,
                            'La contraseña es obligatoria para un usuario nuevo.'
                        );
                    }


                    $user = User::create([
                        'name' =>
                        $data['name'],

                        /*
                         * Temporal mientras username
                         * siga existiendo en la tabla.
                         */
                        'username' =>
                        $this->generateUsername(
                            $data['email']
                        ),

                        'email' =>
                        $data['email'],

                        'password' =>
                        Hash::make(
                            $data['password']
                        ),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Usuario existente
                |--------------------------------------------------------------------------
                */ else {

                    /*
                     * Si estaba eliminado globalmente,
                     * lo restauramos.
                     */
                    if ($user->trashed()) {
                        $user->restore();
                    }


                    /*
                     * Actualizamos el nombre.
                     */
                    $user->name =
                        $data['name'];


                    /*
                     * Password opcional para usuario existente.
                     */
                    if (!empty($data['password'])) {
                        $user->password =
                            Hash::make(
                                $data['password']
                            );
                    }


                    $user->save();
                }


                /*
                |--------------------------------------------------------------------------
                | Evitar membresía duplicada
                |--------------------------------------------------------------------------
                */

                $alreadyBelongs =
                    $user
                    ->companies()
                    ->where(
                        'companies.id',
                        $companyId
                    )
                    ->exists();


                if ($alreadyBelongs) {
                    abort(
                        422,
                        'Ese usuario ya pertenece a esta empresa.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Crear membresía
                |--------------------------------------------------------------------------
                */

                $isSuperAdmin =
                    $data['role']
                    === 'super_admin';


                $user
                    ->companies()
                    ->attach(
                        $companyId,
                        [
                            'role' =>
                            $isSuperAdmin
                                ? 'operator'
                                : $data['role'],

                            'is_admin' =>
                            $isSuperAdmin
                        ]
                    );
            }
        );


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Usuario agregado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    public function edit(User $usuario)
    {
        $currentUser = auth()->user();

        $this->ensureCanManageUsers(
            $currentUser
        );

        $membership =
            $this->membershipForCurrentCompany(
                $usuario
            );


        return view(
            'admin.users.edit',
            [
                'user' => $usuario,
                'membership' => $membership
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        User $usuario
    ) {
        $currentUser = auth()->user();

        $this->ensureCanManageUsers(
            $currentUser
        );


        $membership =
            $this->membershipForCurrentCompany(
                $usuario
            );


        $data = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:100'
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $usuario->id
                    )
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8'
                ],

                'role' => [
                    'required',
                    Rule::in([
                        'operator',
                        'administration',
                        'super_admin'
                    ])
                ]
            ],
            [
                'name.required' =>
                'Ingresá el nombre del usuario.',

                'email.required' =>
                'Ingresá el email.',

                'email.email' =>
                'Ingresá un email válido.',

                'email.unique' =>
                'Ese email ya está siendo utilizado.',

                'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',

                'role.required' =>
                'Debés seleccionar un rol.',

                'role.in' =>
                'El rol seleccionado no es válido.',
            ]
        );


        $willBeSuperAdmin =
            $data['role']
            === 'super_admin';


        /*
        |--------------------------------------------------------------------------
        | Proteger último Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $membership->pivot->is_admin
            &&
            !$willBeSuperAdmin
        ) {

            if (
                $this->superAdminCount()
                <= 1
            ) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'No podés quitar el rol al último Super Admin de la empresa.'
                    );
            }
        }


        DB::transaction(
            function () use (
                $usuario,
                $data,
                $willBeSuperAdmin
            ) {

                /*
                 * Datos globales del usuario.
                 */

                $usuario->name =
                    $data['name'];

                $usuario->email =
                    $data['email'];


                if (!empty($data['password'])) {
                    $usuario->password =
                        Hash::make(
                            $data['password']
                        );
                }


                $usuario->save();


                /*
                 * Datos específicos de la empresa.
                 */

                $companyId = app(
                    CompanyContextService::class
                )->id();


                $usuario
                    ->companies()
                    ->updateExistingPivot(
                        $companyId,
                        [
                            'role' =>
                            $willBeSuperAdmin
                                ? 'operator'
                                : $data['role'],

                            'is_admin' =>
                            $willBeSuperAdmin
                        ]
                    );
            }
        );


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Quitar de la empresa
    |--------------------------------------------------------------------------
    */

    public function destroy(User $usuario)
    {
        $currentUser = auth()->user();

        $this->ensureCanManageUsers(
            $currentUser
        );


        $membership =
            $this->membershipForCurrentCompany(
                $usuario
            );


        /*
        |--------------------------------------------------------------------------
        | No quitarse a sí mismo
        |--------------------------------------------------------------------------
        */

        if (
            $usuario->id ===
            $currentUser->id
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'No podés quitar tu propio usuario de la empresa.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Proteger último Super Admin
        |--------------------------------------------------------------------------
        */

        if (
            (bool) $membership->pivot->is_admin
            &&
            $this->superAdminCount() <= 1
        ) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    'No se puede quitar al último Super Admin de la empresa.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE
        |--------------------------------------------------------------------------
        |
        | No eliminamos User.
        |
        | Solamente quitamos la relación con ESTA empresa.
        |
        */

        $usuario
            ->companies()
            ->detach(
                app(
                    CompanyContextService::class
                )->id()
            );


        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Usuario quitado de la empresa correctamente.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Membresía en empresa activa
    |--------------------------------------------------------------------------
    */

    private function membershipForCurrentCompany(
        User $user
    ) {
        $companyId = app(
            CompanyContextService::class
        )->id();


        $membership =
            $user
            ->companies()
            ->where(
                'companies.id',
                $companyId
            )
            ->first();


        if (!$membership) {
            abort(404);
        }


        return $membership;
    }


    /*
    |--------------------------------------------------------------------------
    | Cantidad de Super Admins
    |--------------------------------------------------------------------------
    */

    private function superAdminCount(): int
    {
        $companyId = app(
            CompanyContextService::class
        )->id();


        return DB::table(
            'company_user'
        )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'is_admin',
                true
            )
            ->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Seguridad
    |--------------------------------------------------------------------------
    */

    private function ensureCanManageUsers(
        User $user
    ): void {

        if (!$user->isSuperAdmin()) {
            abort(403);
        }
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
