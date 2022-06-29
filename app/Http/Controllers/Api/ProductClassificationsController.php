<?php

namespace App\Http\Controllers\Api;

use App\ProductClassification;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class ProductClassificationsController extends Controller
{

    public function all(Request $request)
    {
        try {
            if ($request->has('divisions')) {
                $divisions = $request->divisions;
                $divisions = explode(',', $divisions);
                $classifications = DB::table('styles')
                                    ->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id')
                                    ->select('product_classifications.id', 'product_classifications.jdaName')
                                    ->where('product_classifications.id', '!=', 23)
                                    ->whereIn('styles.division_id', $divisions)
                                    ->distinct()
                                    ->get();
                return ApiResponses::okObject($classifications);
            }
            return ApiResponses::okObject(ProductClassification::select('id', 'jdaName', 'jdaId')->where('id', '!=', 23)->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getAll()
    {
        try {
            $classifications = ProductClassification::all();
            return ApiResponses::okObject($classifications);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
