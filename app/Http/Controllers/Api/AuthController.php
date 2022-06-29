<?php

namespace App\Http\Controllers\Api;

use App\Classes\Eks\EksApi;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Managers\Admin\AdminEksManager;
use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Log;

class AuthController extends Controller
{

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $request['password']=Hash::make($request['password']);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];

        return response($response, 200);
    }

    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->username)->where('active', true)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                    $data = [
                        'access_token' => $token,
                        'accessToken' => $token,
                        'user' => [
                            'displayName' => $user->name,
                            'permissions' => $user->permissions
                        ]
                    ];
                    return ApiResponses::okObject($data);
                } else {
                    return ApiResponses::badRequest("Password missmatch");
                }
            } else {
                return ApiResponses::notFound('User does not exist');
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'User',
                'message' => 'Error al encontrar el recurso: '.$e->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {

        $token = $request->user()->token();
        $token->revoke();

        $response = 'You have been succesfully logged out!';
        return response($response, 200);
    }
}
