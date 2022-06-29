<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\ProductFamily;
use App\Repositories\ProductFamilyRepository;
use Illuminate\Http\Request;
use DB;

class ProductFamilyController extends Controller
{
    protected $rProductFamilie;

    public function __construct()
    {
        $this->rProductFamilie = new ProductFamilyRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $oRequest)
    {
        try {
            $families = $this->rProductFamilie->getAllProductsFamily($oRequest);
            return ApiResponses::okObject($families);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getAll(Request $request)
    {
        try {
            if ($request->has('divisions')) {
                $divisions = $request->divisions;
                $divisions = explode(',', $divisions);
                $families = DB::table('styles')
                                    ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                                    ->select('product_families.id', 'product_families.jdaName')
                                    ->whereIn('styles.division_id', $divisions)
                                    ->distinct()
                                    ->get();
                return ApiResponses::okObject($families);
            }
            return ApiResponses::okObject(ProductFamily::select('id', 'jdaName', 'jdaId')->get());
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $family = $this->rProductFamilie->createProducFamily($request);
            if ($family) {
                return ApiResponses::ok('creado');
            } else {
                return ApiResponses::badRequest();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $family = $this->rProductFamilie->getProductFamily($id);
            if ($family) {
                return ApiResponses::okObject($family);
            } else {
                return ApiResponses::notFound('recurso no encontrado');
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Show families list.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getForDivision($division)
    {
        try {
            $family = $this->rProductFamilie->getForDivision($division);
            if ($family) {
                return ApiResponses::okObject($family);
            } else {
                return ApiResponses::notFound('recurso no encontrado');
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $this->rProductFamilie->updateProductFamily($request);
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
