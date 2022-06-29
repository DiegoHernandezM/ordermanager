<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\ApiResponses;
use Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        //
        if($request->paginated=="true")
            $Role = Role::orderBy("id","ASC")->paginate($request->size);
        else
            $Role["content"] = Role::all();

        return response($Role, 200);
    }

    public function showPolicies(Request $request,$id){
        $policies =  DB::table("role_has_permissions")
            ->join("permissions","role_has_permissions.permission_id","permissions.id")
            ->where("role_has_permissions.role_id","=",$id)->get();

        return response($policies, 200);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000|nullable'
        ]);

        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }

        $Role= Role::where(['name' => $request->name])->first();
        if (empty($Role)) {
            $Role = Role::create(['name' => $request->name,'description'=>$request->description]);
            if($request->policies){
                foreach ($request->policies as $value){
                    $Role->givePermissionTo($value);
                }
            }
            return ApiResponses::created("Rol creado con exito");
        }
        return ApiResponses::found("El Rol ingresado ya existe");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Role  $role
     * @return Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function edit(Request $request,$id)
    {
        //
        $role = Role::find($id);

        if (empty($role)) {
            return ApiResponses::notFound("El Rol no existe.");
        }

        return response($role, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param Role $role
     * @return Response
     */
    public function update(Request $request,int $id, Role $role)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'string|max:1000|nullable'
        ]);

        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }
        $role = Role::find($id);

        if (empty($role)) {
            return ApiResponses::notFound("El Rol no existe.");
        }

        $role->name = $request->name;
        $role->description = $request->description;
        $role->save();
        $role->syncPermissions();

        if($request->policies){
            foreach ($request->policies as $value){
                $role = Role::findById($id);
                $role->givePermissionTo($value);
                $role->save();
            }
        }

        return ApiResponses::ok("Rol editado con éxito");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Role $role
     * @param $id
     * @return Response
     */
    public function destroy(Request $request,Role $role,$id)
    {
        //
        $validator = Validator::make(["id"=>$id], [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }
        $role = Role::find($id);
        if (empty($role)) {
            return ApiResponses::notFound("El rol no existe.");
        }
        $role->delete();
        return ApiResponses::ok("El rol ha sido eliminado con exito.");
    }
}
