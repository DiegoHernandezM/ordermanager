<?php

namespace App\Http\Controllers\Api;

use App\Department;
use App\Division;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;

class DivisionController extends Controller
{
    public function departments(Division $division, Request $request)
    {
        try {
            return ApiResponses::okObject($division->departments()->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getDepartments(Request $request)
    {
        try {
            $divisions = $request->divisions;
            $divisions = explode(',', $divisions);
            $divisionsModels = Division::select('id', 'name')
                ->whereIn('id', $divisions)->with(['departments' => function ($q) {
                    $q->select('id', 'name', 'division_id', 'jda_id','jda_name');
                }])->get();
            return ApiResponses::okObject($divisionsModels->toArray());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function categories(Division $division, Request $request)
    {
        try {
            return ApiResponses::okObject($division->departments()->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function all(Request $request)
    {
        try {
            return ApiResponses::okObject(Division::with('departments:id,name,division_id')
              ->select('id', 'name', 'jda_id')->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getDepartmentByDivisionId(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'order_group_id' => 'required|integer',
                'divisions' => 'string|max:16'
            ]);
            if ($v->fails()) {
                return ApiResponses::badRequest();
            }
            $query = DB::select('select distinct d.name, d.id from `lines` l join orders o on o.id = l.order_id
            join departments d on d.id = l.department_id
            where o.order_group_id = '.$request->order_group_id.' and
            l.division_id in (' .$request->divisions. ')');
            return ApiResponses::okObject($query);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function allDivisions()
    {
        try {
            return ApiResponses::okObject(Division::with('departments:id,name,division_id,jda_id,jda_name')
                ->select('id', 'name', 'jda_id', 'jda_name')->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
