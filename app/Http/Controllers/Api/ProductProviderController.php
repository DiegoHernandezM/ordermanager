<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\ProductProviderRepository;
use Illuminate\Http\Request;

class ProductProviderController extends Controller
{
    protected $rProductProvider;

    public function __construct()
    {
        $this->rProductProvider = new ProductProviderRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            return ApiResponses::okObject($this->rProductProvider->getAllProviders($request));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Busca proveedor por nombre o numero
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        try {
            return ApiResponses::okObject($this->rProductProvider->search($request));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
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
