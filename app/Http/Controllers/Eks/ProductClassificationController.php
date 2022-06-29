<?php

namespace App\Http\Controllers\Eks;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductClassificationRequest;
use App\Repositories\Eks\ProductClassificacionRepository;
use Illuminate\Http\Request;
use Log;

class ProductClassificationController extends Controller
{
    protected $rProductClassification;
    protected $cApiResponse;

    public function __construct()
    {
        $this->rProductClassification = new ProductClassificacionRepository();
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
     * @param ProductClassificationRequest $oRequest
     * @return \Illuminate\Http\Response
     */
    public function store(ProductClassificationRequest $oRequest)
    {
        try {
            $this->rProductClassification->createProductClassification($oRequest);
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductClassificationRequest $oRequest, $id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $classification = $this->rProductClassification->updateProductClassification($id ,$oRequest);

            return $this->cApiResponse->okObject($classification);

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
