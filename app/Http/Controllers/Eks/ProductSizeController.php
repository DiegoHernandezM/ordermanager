<?php

namespace App\Http\Controllers\Eks;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSizeRequest;
use App\Repositories\Eks\ProductSizeRepository;
use Illuminate\Http\Request;

class ProductSizeController extends Controller
{

    protected $rProductSize;
    protected $cApiResponse;

    public function __construct()
    {
        $this->rProductSize = new ProductSizeRepository();
        $this->cApiResponse = new ApiResponses();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function store(ProductSizeRequest $oRequest)
    {
        try {
            $this->rProductSize->createProductSize($oRequest);
            return $this->cApiResponse->created();
        } catch(\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
     * @param  \Illuminate\Http\Request  $oRequest
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductSizeRequest $oRequest, $id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $family = $this->rProductSize->updateProductSize($id ,$oRequest);

            return $this->cApiResponse->okObject($family);

        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
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
