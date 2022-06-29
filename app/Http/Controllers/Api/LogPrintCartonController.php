<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\LogPrintCartonRepository;
use Illuminate\Http\Request;


class LogPrintCartonController extends Controller
{
    protected $cLogcartonRepository;

    public function __construct()
    {
        $this->cLogcartonRepository = new LogPrintCartonRepository();
    }

    /**
     * @param $barcode
     * @return \Illuminate\Http\Response
     */
    public function getByBarcode($barcode)
    {
        try {
            $logs = $this->cLogcartonRepository->getLog($barcode);
            if ($logs) {
                return ApiResponses::okObject($logs);
            } else {
                return ApiResponses::notFound();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
