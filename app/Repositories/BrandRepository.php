<?php

namespace App\Repositories;

use App\Brand;
use App\Http\Controllers\ApiResponses;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Log;

class BrandRepository extends BaseRepository
{
    protected $mBrand;


    public function _construct()
    {
        $this->mBrand = new Brand();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBrands()
    {
        try {
            $brands = Brand::all();
            return response()->json(['brands' =>  $brands], 200);
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Brand',
                'message' => 'Error al obtener el recurso: '.$e->getMessage(),
            ]);
        }
    }
}
