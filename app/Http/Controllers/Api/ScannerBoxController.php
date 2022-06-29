<?php

namespace App\Http\Controllers\Api;

use App\Carton;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Log;
use App\LogPrintCarton;
use App\Repositories\LogPrintCartonRepository;
use App\Repositories\ScannerBoxRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zebra\Client;
use Validator;

class ScannerBoxController extends Controller
{
    protected $cApiResponse;
    protected $rScannerBox;
    protected $cLogPrintCarton;

    public function __construct()
    {
        $this->cApiResponse = new ApiResponses();
        $this->rScannerBox = new ScannerBoxRepository();
        $this->cLogPrintCarton = new LogPrintCartonRepository();
    }

    /**
     * @param $barcode
     * @return \Illuminate\Http\Response
     */
    public function getInfoBarCode(Request $request, $barcode)
    {
        try {
            $oValidator = Validator::make(['barcode' => $barcode], [
                'barcode' => 'required|alpha_dash',
            ]);
            if ($oValidator->fails()) {
                return $this->cApiResponse->badRequest();
            }

            $getInfoScan = $this->rScannerBox->getInfoScan($barcode);

            if ($getInfoScan != null) {
                if ($request->has('ipAddress')) {
                    $client = new Client($request->ipAddress);
                    $client->send($getInfoScan->zpl);

                    // Crea log de impresion de carton
                    $this->cLogPrintCarton->createLog($barcode);

                    return $this->cApiResponse->okObject([$getInfoScan, "message" => "imprimiendo en: ".$request->ipAddress]);
                } else {
                    return $this->cApiResponse->okObject($getInfoScan);
                }
            } else {
                return $this->cApiResponse->notFound();
            }
        } catch (\Exception $e) {
            return $this->cApiResponse->internalServerError($e->getMessage());
        }
    }
}
