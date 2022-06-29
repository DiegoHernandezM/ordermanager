<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Repositories\ReportRepository;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;
use SendGrid\Response;

class ReportController extends Controller
{
    protected $cReport;

    public function __construct()
    {
        $this->cReport = new ReportRepository();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getWavesToday()
    {
        try {
            return $this->cReport->getWaveTodayReport();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getWavesWeek()
    {
        try {
            return $this->cReport->getWaveWeekReport();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getCartonsToday()
    {
        try {
            return $this->cReport->getCartonTodayReport();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getCartonsWeek()
    {
        try {
            return $this->cReport->getCartonWeekReport();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /** Obtiene el reporte de plan de olas
     * @return \Illuminate\Http\Response
     */
    public function getPlannedWaves(Request $request)
    {
        try {
            $plannedWaves = $this->cReport->getPlannedWaves($request);
            if (!$plannedWaves === false) {
                return $plannedWaves->toBrowser();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getWaveData()
    {
        try {
            $allDataWave = $this->cReport->getWaveDataJson();
            return ApiResponses::okObject($allDataWave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function getWaveDataWithParams(Request $request)
    {
        try {
            return $allWaveDataWithParams = $this->cReport->getWaveDataJsonWithParams($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $skuArray
     * @return \Illuminate\Http\Response
     */
    public function validatePpk(Request $request)
    {
        try {
            $validate = $this->cReport->checkPpk($request->data);
            return ApiResponses::okObject($validate);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getWaveOrdersReport(Request $request)
    {
        try {
            $report = $this->cReport->getDataWavesOrderFinished($request);
            return ApiResponses::okObject($report);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getShipmentReportByOrderGroup(Request $request)
    {
        try {
            return $this->cReport->getShipmentsByOrderGroup($request->orderGroup);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
