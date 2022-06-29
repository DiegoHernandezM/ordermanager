<?php

namespace App\Http\Controllers\Eks;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPriorityRequest;
use App\Repositories\Eks\ProductPriorityRepository;
use Illuminate\Http\Request;

class ProductPriotityController extends Controller
{
    protected $rProductPriority;
    protected $cApiResponse;

    public function __construct()
    {
        $this->rProductPriority = new ProductPriorityRepository();
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
    public function store(ProductPriorityRequest $oRequest)
    {
        try {
            $this->rProductPriority->createProductPriority($oRequest);
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
    public function update(ProductPriorityRequest $oRequest, $id)
    {
        try {
            $oValidator = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }
            $priority = $this->rProductPriority->updateProductPriority($id ,$oRequest);

            return $this->cApiResponse->okObject($priority);

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
