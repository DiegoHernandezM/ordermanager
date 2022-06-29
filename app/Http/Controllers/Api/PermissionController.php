<?php

namespace App\Http\Controllers\Api;

use App\AccessType;
use App\Http\Controllers\Controller;
use App\PermissionsHasAccess;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\ApiResponses;
use Validator;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->paginated=="true") {
            $permission = Permission::orderBy("id", "ASC")->paginate($request->size);
        } else {
            $permission["content"] = Permission::where("guard_name", "=", "web")->where('name', '!=', '/')->get();
        }

        return response($permission, 200);
    }

    /**
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function show(Request $request)
    {
        $seeds = DB::table('access_types')
            ->join('application_types', 'access_types.id', '=', 'application_types.access_type')
            ->paginate($request->size);

        return response($seeds, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        //
        $permission = Permission::find($id);


        if (empty($permission)) {
            return ApiResponses::notFound("El permiso no existe.");
        }
        $aPermissions=$permission->toArray();

        $access = DB::table('permissions_has_accesses')
            ->join('application_types', 'permissions_has_accesses.id_access', '=', 'application_types.id')
            ->join('access_types', 'application_types.access_type', '=', 'access_types.id')
            ->where('permissions_has_accesses.id_permission', '=', $id)
            ->get()
            ->toArray();
        $aPermissions['accessPolicy']=$access;
        //var_dump($aPermissions);
        return response($aPermissions, 200);
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000',
            'access' => 'required|array'
        ]);
        //if (true) {
        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }

        $permission = Permission::where(['name' => $request->name])->first();
        if (empty($permission)) {
            $permission = Permission::create(['name' => $request->name,'description'=>$request->description,"access_type"=>1,"type"=>2]);
            $this->addPermissionsHasAccess($permission->id, $request->access);
            return ApiResponses::created("Permiso creado con éxito");
        }

        return ApiResponses::found("El permiso ya existe");
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'access' => 'required|array',
            'name' => 'required|string|max:255',
        ]);

        //if (true) {
        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }

        $permission = Permission::find($id);
        if (empty($permission)) {
            return ApiResponses::notFound("El permiso no existe.");
        }
        $model = PermissionsHasAccess::where('id_permission', '=', $id)->delete();

        $this->addPermissionsHasAccess($id, $request->access);
        $permission->name = $request->name;
        $permission->description = $request->description;
        $permission->save();


        //return ApiResponses::okObject($permission);
        return ApiResponses::ok("Se actualizó la política");
    }

    /**
     * @param $id
     * @return Response
     */
    public function delete($id)
    {
        $validator = Validator::make(["id"=>$id], [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->all());
        }

        $permission = Permission::find($id);
        if (empty($permission)) {
            return ApiResponses::notFound("El permiso no existe.");
        }
        $permission->delete();
        return ApiResponses::ok("Política actualizada con éxito.");
    }

    private function addPermissionsHasAccess($permission_id, $r_access)
    {
        $r_access = array_unique($r_access);
        if (count($r_access)>1) {
            foreach ($r_access as $value) {
                $key = explode(':', $value)[1];
                $access = DB::table('application_types')->where('actionKey', '=', $key)->first();
                $model = PermissionsHasAccess::create(['id_permission'=>$permission_id,'id_access'=>$access->id,'access'=>$value]);
            }
        } else {
            $actionKey  = explode(':', $r_access[0])[1];
            $access = DB::table('application_types')->where('actionKey', '=', $actionKey)->first();
            $model = PermissionsHasAccess::create(['id_permission'=>$permission_id,'id_access'=>$access->id,'access'=>$r_access[0]]);
        }
    }
}
