<?php

namespace App\Http\Controllers\Api;

use App\ProductClasses;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassesController extends Controller
{

    public function all(Request $request)
    {
        try {
            if ($request->has('departments')) {
                $departments = $request->departments;
                return ApiResponses::okObject(ProductClasses::whereNotIn('department_id', explode(',', $departments))
                    ->select('id', 'jdaName')
                    ->get());
            } else {
                return ApiResponses::okObject(ProductClasses::select('id', 'jdaName','jdaId','department_id')->get());
            }
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getClassesByDepartmentId(Request $request)
    {
        try {
            if ($request->has('department')) {
                $department = $request->department;
                $classes = ProductClasses::where('department_id', $department)
                    ->select('id', 'jdaName', 'jdaId')
                    ->get();
                return ApiResponses::okObject($classes);
            }
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
