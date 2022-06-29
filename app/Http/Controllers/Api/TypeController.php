<?php

namespace App\Http\Controllers\Api;

use App\ProductType;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TypeController extends Controller
{

    public function all(Request $request)
    {
        try {
            if ($request->has('classes')) {
                $classes = $request->classes;
                return ApiResponses::okObject(ProductType::whereNotIn('clasz_id', explode(',', $classes))
                    ->select('id', 'jdaName')
                    ->get());
            } else {
                return ApiResponses::okObject(ProductType::select('id', 'jdaName', 'jdaId', 'clasz_id')->get());
            }
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getTypesByClassesId(Request $request)
    {
        try {
            if ($request->has('class')) {
                $class = $request->class;
                $productTypes = ProductType::where('clasz_id', $class)
                    ->select('id', 'jdaName', 'jdaId')
                    ->get();
                return ApiResponses::okObject($productTypes);
            }
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
