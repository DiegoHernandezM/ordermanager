<?php

namespace App\Http\Controllers\Api;

use App\StoreDepartment;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Store;
use App\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class StoreDepartmentsController extends Controller
{

    public function get(Request $request)
    {
        try {
            $storeDepartments = StoreDepartment::orderBy('updated_at', 'desc')->get();
            return ApiResponses::okObject($storeDepartments);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function create(Request $request)
    {
        try {
            $userId = Auth::id();
            $userName = Auth::user()->name;
            if (!$request->has('department_id')) {
                $v = Validator::make($request->all(), StoreDepartment::$rules_division);
                if ($v->fails()) {
                    return ApiResponses::badRequest($v->errors());
                }
                $store = Store::find($request->store_id);
                $departments = Department::where('division_id', (Int)$request->division)->get();
                $array = [];
                foreach ($departments as $dep) {
                    $storeDepartment = StoreDepartment::updateOrCreate(
                        ['store_id' => $request->store_id, 'department_id' => $dep->id],
                        [
                            'storeNumber' => $store->number,
                            'departmentNumber' => $dep->jda_name,
                            'block_until' => $request->block_until ?? NULL,
                            'user_id' => $userId,
                            'user_name' => $userName
                        ]
                    );
                    $array[] = $storeDepartment;
                }
                return ApiResponses::okObject($array);
            } else {
                $v = Validator::make($request->all(), StoreDepartment::$rules);
                if ($v->fails()) {
                    return ApiResponses::badRequest($v->errors());
                }
                $store = Store::find($request->store_id);
                $dep = Department::find($request->department_id);
                $storeDepartment = StoreDepartment::updateOrCreate(
                    ['store_id' => $request->store_id, 'department_id' => $dep->id],
                    [
                        'storeNumber' => $store->number,
                        'departmentNumber' => $dep->jda_name,
                        'block_until' => $request->block_until ?? NULL,
                        'user_id' => $userId,
                        'user_name' => $userName
                    ]
                );
                return ApiResponses::okObject($storeDepartment);
            }


        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function delete(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [ 'id' => 'required|integer' ]);
            if ($v->fails()) {
                return ApiResponses::badRequest($v->errors());
            }
            $storeDepartment = StoreDepartment::find($request->id);
            $storeDepartment->delete();
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
