<?php

namespace App\Http\Controllers\Api;

use App\Department;
use App\Mail\EmailAmazonSes;
use App\Team;
use App\UserTeam;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Classes\Eks\EksApi;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\ResetPasswordUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\User;
use App\Classes\Mail\MailSendGrill;
use App\Managers\SendMailsManager;
use Illuminate\Support\Facades\Hash;
use Log;
use Validator;
use Illuminate\Support\Str;
use Auth;

class UserController extends Controller
{
    protected $mUser;
    protected $cMail;
    protected $mResetPassword;
    protected $aConfig;
    protected $cApiResponse;
    protected $mUserTeam;
    protected $mTeam;
    protected $cAwsMail;
    protected $mail;

    public function __construct(User $user, ResetPasswordUser $reset)
    {
        $this->mUser = $user;
        $this->mResetPassword = $reset;
        $this->cMail = new MailSendGrill();
        $this->aConfig = config('systems');
        $this->cApiResponse = new ApiResponses();
        $this->mUserTeam = new UserTeam();
        $this->mTeam = new Team();
        $this->cAwsMail = new EmailAmazonSes();
        $this->mail = new SendMailsManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'order' => 'max:30|in:id,name,sucursal,created_at,updated_at,deleted_at',
                'search' => 'max:100',
                'extension' => 'between:1,999',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "message" => json_encode($oValidator->errors())], 400);
            }

            $sFiltro = $oRequest->input('search', false);
            $aUsers = $this->mUser
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('name', 'like', "%$sFiltro%")
                                ->orWhere('extension', '=', "%$sFiltro%")
                                ->orWhere('email', 'like', "%$sFiltro%");
                        }
                    }
                )
                ->orderBy($oRequest->input('order', 'id'), $oRequest->input('sort', 'asc'));
            if ($oRequest->pagination === 'false') {
                $aUsers = $aUsers->get();
            } else {
                $aUsers = $aUsers->paginate((int) $oRequest->input('per_page', 25));
            }
            return response()->json(["data" => ["users" => $aUsers]], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al obtener el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'name' => 'required|min:5',
                'email' => 'required|email',
                'department' => 'array',
                'password' => 'required|min:6',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $user =  new $this->mUser();
            $user->name = $oRequest->name;
            $user->email = $oRequest->email;
            $user->active = 1;
            $user->extension = $oRequest->extension;
            $user->password = Hash::make($oRequest->password);
            $user->save();

            //Asignacion de roles y politicas a usuario
            $this->updateRoles($user->id, $oRequest->authority['roles']);
            $this->updatePolicies($user->id, $oRequest->authority['policies']);

            $user->givePermissionTo(Permission::where('name', '/')
                ->where('guard_name', 'web')
                ->first());

            $user->givePermissionTo(Permission::where('name', '/perfil')
                ->where('guard_name', 'web')
                ->first());

            $m = new SendMailsManager;
            $m->newUserMail($user->name, $user->email, $oRequest->password);

            return response()->json([
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al crear el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        try {
            $user = Auth::user();
            $user->displayName = $user->name;
            return response()->json([
                'user' => $user,
                'permissions' => $user->permissions,
                'role' => $user->roles
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al obtener el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        try {
            $user = $this->mUser->find($id);
            $departments = [];
            if ($user->department) {
                foreach (json_decode($user->department) as $depto) {
                    $departments[] = Department::find($depto);
                }
            }
            $user->department = $departments;
            if ($user) {
                return response()->json([
                    'user' => $user
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Recurso no existente'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al obtener el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'id' => 'required|numeric',
                'name' => 'required|min:5',
                'email' => 'required|email',
            ]);

            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $departments = [];

            if ($oRequest->department) {
                foreach ($oRequest->department as $depto) {
                    $departments[] = $depto['id'];
                }
            }

            $user = $this->mUser->find($oRequest->id);
            $user->name = $oRequest->name;
            $user->email = $oRequest->email;
            $user->extension = $oRequest->extension;
            $user->active = $oRequest->active;
            $user->department = ($departments) ? json_encode($departments) : $user->department;
            $user->update();

            //Actualizacion de roles y politicas
            $params['roles'] = $this->updateRoles($oRequest->id, $oRequest->authority['roles']);
            $params['policies'] = $this->updatePolicies($oRequest->id, $oRequest->authority['policies']);

            return response()->json([
                'user' => $user,
                'params' => $params
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al actualizar el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $user = $this->mUser->find($id);
            $user->active = false;
            $user->save();

            return response()->json([
                'message' => 'Usuario desactivado'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al eliminar el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTokenResetPassword(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'email' => 'required|email|exists:users'
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $user = $this->mUser->where('email', $oRequest->email)->first();
            $alreadySentToday = $this->mResetPassword->where('email', $user->email)->whereDate('created_at', Carbon::today())->first();
            if (empty($alreadySentToday)) {
                if ($user) {
                    $token = Str::random(16);

                    $reset = $this->mResetPassword->create([
                        'email' => $user->email,
                        'token' => $token,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                    ]);

                    $body = [
                        $user,
                        $reset->token
                    ];

                    // Se envia correo por Amazon SES
                    $this->mail->sendTokenResetPassword($user->name, $user->email, $token);

                    // Ya no se usa SendGrid
                    //$this->cMail->buid('mail.reset_password', 'no-replay@ccp.com', $user->email, $user->name, 'No-Reply', 'Token Reset Password', $body);

                    return response()->json([
                        'message' => 'Correo enviado'
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Usuario no encontrado'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Ya se realizó un intento de restablecimiento el día de hoy, verifi.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al crear el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function validTokenPassword($token)
    {
        try {
            $oValidator = Validator::make(['token' => $token], [
                'token' => 'required'
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }
            $showToken = $this->mResetPassword->where('token', $token)->first();

            if ($showToken != null) {
                $url = $this->aConfig['oms']['url'] . '/changepassword/' . $token;
                return redirect()->intended($url);
            } else {
                return  response()->json([
                    'code' => 401,
                    'message' => false,
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al encontrar el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'password' => 'required|confirmed|min:6'
            ]);

            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "message" => json_encode($oValidator->errors())], 400);
            }

            $reset = $this->mResetPassword->where('token', $oRequest->token)->first();
            $createdToken = Carbon::parse($reset->created_at);
            $today = Carbon::now();
            $diffDays = $createdToken->diffInDays($today);

            if ($diffDays > 1) {
                return response()->json(['message' => 'Solicite un nuevo token'], 400);
            } else {
                $user = $this->mUser->where('email', $reset->email)->first();
                $user->password = Hash::make($oRequest->password);
                $user->save();
            }

            return  response()->json([
                'code' => 200,
                'message' => 'Password reseteado',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al actualizar el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $oRequest)
    {
        try {
            if (!(Hash::check($oRequest->old_password, Auth::user()->password))) {
                return response()->json(['message' => 'La contraseña no coincide con los registros'], 400);
            }
            if (strcmp($oRequest->old_password, $oRequest->new_password) == 0) {
                return response()->json(['message' => 'La nueva contraseña tiene que ser diferente a la anterior'], 400);
            }
            $oValidator = Validator::make($oRequest->all(), [
                'old_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
            ]);
            if ($oValidator->fails()) {
                return response()->json(['message' => json_encode($oValidator->errors())], 400);
            }

            $user = Auth::user();
            $user->password = Hash::make($oRequest->new_password);
            $user->save();

            return $user;
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al actualizar el recurso: ' . $e->getMessage(),
            ]);
        }
    }


    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordByAdmin(Request $oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'new_password' => 'required|string|min:6|confirmed',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "data" => ["errors" => $oValidator->errors()]]);
            }

            $user = $this->mUser->where('email', $oRequest->email)->first();
            $user->password = Hash::make($oRequest->new_password);
            $user->save();

            return $user;
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al actualizar el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getAuthority(Request $request)
    {
        $response['access'] = false;
        $user   = User::find(Auth::id());
        $roles = $user->roles->pluck('id');
        $permissions = $user->permissions->pluck("id");

        $response['rol'] = $roles;
        $response['permissions'] = $permissions;
        $AccessByPolicy = DB::table('model_has_permissions')
            ->select('permissions_has_accesses.id_access', 'permissions_has_accesses.access')
            ->join('permissions_has_accesses', 'model_has_permissions.permission_id', '=', 'permissions_has_accesses.id_permission')
            ->whereIn('model_has_permissions.permission_id', $permissions)
            ->whereIn('permissions_has_accesses.access', ['*:*', $request->authority])
            ->get()->toArray();
        $response['ABP'] = $AccessByPolicy;

        if (count($AccessByPolicy)) {
            $response['access'] = true;
        } else {
            $accessByRol = DB::table('role_has_permissions')
                ->select('permissions_has_accesses.id_access', 'permissions_has_accesses.access')
                ->join('permissions_has_accesses', 'role_has_permissions.permission_id', '=', 'permissions_has_accesses.id_permission')
                ->whereIn('role_has_permissions.role_id', $roles)
                ->whereIn('permissions_has_accesses.access', ['*:*', $request->authority])
                ->get()->toArray();
            $response['ABR'] = $accessByRol;
            if (count($accessByRol)) {
                $response['access'] = true;
            }
        }

        return ApiResponses::okObject($response);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function getAuthorities(Request $request, $id)
    {
        $user = User::find($id);
        $response['roles'] = $user->roles;
        $response['policies'] = $user->permissions;
        $data = ['user' => $response];

        return ApiResponses::okObject($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function checkPermission(Request $request)
    {
        return ApiResponses::okObject(Auth::user()->hasAnyPermission(['Full Access', $request->match]));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getPermissions(Request $request)
    {
        return ApiResponses::okObject(Auth::user()->permissions);
    }


    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function accessByUserId(Request $request, $id)
    {
        $user = User::find($id);
        $response['roles']      = $user->roles;
        $response['policies']   = $user->permissions;

        /*$response['roles']=DB::table('model_has_roles')
            ->join('roles','model_has_roles.role_id','=','roles.id')
            ->where('model_has_roles.model_id','=',$id)->get();*/

        $response['rolesPro'] = DB::table('model_has_permissions')
            ->select('model_has_permissions.permission_id', 'permissions_has_accesses.access')
            ->join('permissions_has_accesses', 'model_has_permissions.permission_id', '=', 'permissions_has_accesses.id_permission')
            //->whereIn('model_has_permissions.permission_id',$user->permissions)
            //->whereIn('permissions_has_accesses.access',['*:*',$request->authority])
            ->get()->toArray();

        $response['policiesPro'] = DB::table('role_has_permissions')
            ->select('role_has_permissions.role_id', 'permissions_has_accesses.access')
            ->join('permissions_has_accesses', 'role_has_permissions.permission_id', '=', 'permissions_has_accesses.id_permission')
            //->whereIn('role_has_permissions.role_id',$user->roles)
            //->whereIn('permissions_has_accesses.access',['*:*',$request->authority])
            ->get()->toArray();

        /*$response['policies']=DB::table('model_has_permissions')
            ->join('permissions','model_has_permissions.permission_id','=','permissions.id')
            ->where('model_has_roles.model_id','=',$id)->get();*/

        return ApiResponses::okObject($response);
    }

    /**
     * @param $id
     * @param $roles
     * @return array
     */
    private function updateRoles($id, $roles)
    {
        $params = [];
        if (count($roles) > 0) {
            $user = User::find($id);
            $delete = DB::table('model_has_roles')->where('model_id', '=', $id);
            $delete->delete();
            foreach ($roles as $rol) {
                $user->assignRole(Role::find($rol));
            }
        }
        return $params;
    }

    /**
     * @param $id
     * @param $policies
     * @return array
     */
    private function updatePolicies($id, $policies)
    {
        $params = [];
        if (count($policies) > 0) {
            $user = User::find($id);
            $delete = DB::table('model_has_permissions')->where('model_id', '=', $user->id);
            $delete->delete();
            foreach ($policies as $policy) {
                $user->givePermissionTo(Permission::find($policy));
            }
        }
        return $params;
    }

    /**
     * @return Response
     */
    public function getUsersRol($id)
    {
        try {
            $users = $this->mUser->leftJoin('user_team', 'users.id', '=', 'user_team.id_operator')
                ->select('users.*', 'user_team.id_team')->distinct()->get();
            $usersRol = [];
            foreach ($users as $user) {
                foreach ($user->roles as $role) {
                    if ($role->id === (int)$id) {
                        if ($user->id_team != null) {
                            $team = $this->mTeam->find($user->id_team);
                        }
                        $usersRol[] = [
                            'id'    => $user->id,
                            'name'  => $user->name,
                            'rol'   => $role->id,
                            'team'  => $team->id ?? null,
                            'teamName'  => $team->name ?? null,
                        ];
                    }
                }
            }
            return ApiResponses::okObject($usersRol);
        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }
}
