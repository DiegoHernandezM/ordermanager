<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\StyleRepository;
use Illuminate\Http\Request;
use Validator;
use Log;

class StyleController extends Controller
{
    //

    protected $mStyleRepository;

    public function __construct()
    {
        $this->mStyleRepository = new StyleRepository();
    }

    /**
     * Guarda un estilo.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */

    public function create(Request $oRequest)
    {
        try {
            $newStyle = $this->mStyleRepository->createStyle($oRequest->all());
            return ApiResponses::okObject($newStyle);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Get styles by Order Group.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */

    public function get(Request $oRequest)
    {
        try {
            $getStyles = $this->mStyleRepository->getStyles($oRequest);
            return ApiResponses::okObject($getStyles);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
