<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Managers\RequestManager;
use App\Wave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use config;

class MessageItAppsController extends Controller
{
    public function sendWaveDestiny(Request $oRequest)
    {
        try {
            $aResponse = [];
            $message = new RequestManager();
            if (count($oRequest->waves) > 0) {
                foreach ($oRequest->waves as $wave) {
                    $findWave = Wave::find($wave);
                    if ($findWave && $findWave->status === Wave::COMPLETED) {
                        $aData = [
                            'olaID' => $wave,
                            'destino' => $oRequest->destino
                        ];
                        $response = $message->send('basic', 'saalma', '/ola/destino/fin', 'POST', '', 'TEST', 'TEST', '', [], $aData);
                        if ($response->status_code == 200) {
                            $sMessage = json_decode($response->response);
                            $aResponse[] = [
                                'wave' => $wave,
                                'destino' => $oRequest->destino,
                                'response' => $sMessage->mensaje ?? null,
                            ];
                        }
                    }
                }
                return response()->json($aResponse);
            }
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendDestinyWave(Request $oRequest)
    {
        try {
            $aResponse = [];
            $message = new RequestManager();
            if ($oRequest->has('wave')) {
                $wave = Wave::find($oRequest->wave);
                $ordergroup = $wave->ordergroup;
                $orders = $ordergroup->orders;
                foreach ($orders as $key => $ord) {
                    $aData = [
                        'olaID' => $wave->id,
                        'destino' => $ord->storeNumber
                    ];
                    $response = $message->send('basic', 'saalma', '/ola/destino/fin', 'POST', '', 'TEST', 'TEST', '', [], $aData);
                    if ($response->status_code == 200) {
                        $sMessage = json_decode($response->response);
                        $aResponse[] = [
                            'wave' => $wave->id,
                            'destino' => $ord->storeNumber,
                            'response' => $sMessage->mensaje ?? null,
                        ];
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return $e->getMessage();
        }
    }
}
