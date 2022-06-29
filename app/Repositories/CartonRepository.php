<?php

namespace App\Repositories;

use App\Carton;
use App\CartonLine;
use App\Http\Controllers\ApiResponses;
use App\Line;
use App\Managers\Admin\AdminSAALMAManager;
use App\Managers\Admin\AdminWamasFileManager;
use App\Order;
use App\Repositories\CartonLineRepository;
use App\Repositories\ScannerBoxRepository;
use Zebra\Client;
use App\Wave;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CartonRepository extends BaseRepository
{
    const AREA = array(
        1 => 'SORTER1',
        2 => 'PTL',
        3 => 'SORTER3',
        4 => 'TODOS',
        5 => 'TRF',
    );
    const AREAS = [
        'SORTER1',
        'PTL',
        'SORTER3',
    ];
    protected $mCarton;
    protected $model = 'App\Carton';
    protected $rScannerBox;

    public function __construct()
    {
        $this->cartonLineRepository = new CartonLineRepository();
        $this->mCarton = new Carton();
        $this->rScannerBox = new ScannerBoxRepository();
    }

    /**
     * @param $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCartons($oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'per_page' => 'numeric|between:5,1000',
                'order' => 'max:30|in:id',
                'search' => 'max:100',
                'extension' => 'between:1,999',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "message" => json_encode($oValidator->errors())], 400);
            }
            $aCartons = "";
            $getArea = CartonRepository::AREA[$oRequest->area];
            $sFiltro = $oRequest->input('search', false);
            $style = ($oRequest->style) ? $oRequest->style : null;
            $sku = ($oRequest->sku) ? $oRequest->sku : null;
            $wave = ($oRequest->wave) ? $oRequest->wave : null;
            if ($oRequest->dateInit !== null && $oRequest->dateEnd !== null && $oRequest->area == 5) {
                $aCartons = $this->mCarton
                    ->whereNull('transferStatus')
                    ->whereNull('transferNum')
                    ->orderBy('cartons.updated_at', 'desc')
                    ->get();

                $result = collect($aCartons);
                if (isset($oRequest->orderBy)) {
                    if ($oRequest->orderDirection == 'asc') {
                        $result = $result->sortBy($oRequest->orderBy);
                    } else {
                        $result = $result->sortByDesc($oRequest->orderBy);
                    }
                }
                $result = $result->paginate((int) $oRequest->input('per_page'));
                return $result;
            } elseif ($oRequest->dateInit !== null && $oRequest->dateEnd !== null && $sku == null && $style == null) {
                $aCartons = $this->mCarton
                    ->where(function ($q) use ($getArea) {
                        $allArea = CartonRepository::AREA;
                        unset($allArea[4]);
                        unset($allArea[5]);
                        if ($getArea !== CartonRepository::AREA[4]) {
                            return $q->where('area', $getArea);
                        } else {
                            return $q->whereIn('area', $allArea);
                        }
                    })
                    ->where(function ($q) use ($oRequest) {
                        $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
                        $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : null;
                        if ($dateInit !== null || $dateEnd !== null) {
                            return $q
                                ->wherebetween('created_at', [$dateInit, $dateEnd]);
                        }
                    })
                    ->where(function ($q) use ($wave) {
                        if ($wave !== null) {
                            return $q->where('wave_id', $wave);
                        }
                    })
                    ->where(
                        function ($q) use ($sFiltro) {
                            if ($sFiltro !== false) {
                                return $q
                                    ->orWhere('waveNumber', 'like', "%$sFiltro%")
                                    ->orWhere('wave_id', '=', $sFiltro)
                                    ->orWhere('store', '=', $sFiltro)
                                    ->orWhere('barcode', 'like', "%$sFiltro%");
                            }
                        }
                    )
                    ->orderBy('updated_at', 'desc')
                    ->get();

                $result = collect($aCartons);
                if (isset($oRequest->orderBy)) {
                    if ($oRequest->orderDirection == 'asc') {
                        $result = $result->sortBy($oRequest->orderBy);
                    } else {
                        $result = $result->sortByDesc($oRequest->orderBy);
                    }
                }
                $result = $result->paginate((int) $oRequest->input('per_page'));
                return $result;
            } elseif ($oRequest->dateInit !== null && $oRequest->dateEnd !== null || $style !== null || $sku !== null) {
                $aCartons = $this->mCarton
                    ->join('carton_lines', 'carton_lines.carton_id', '=', 'cartons.id')
                    ->where(function ($q) use ($getArea) {
                        $allArea = CartonRepository::AREA;
                        unset($allArea[4]);
                        unset($allArea[5]);
                        if ($getArea !== CartonRepository::AREA[4]) {
                            return $q->where('cartons.area', $getArea);
                        } else {
                            return $q->whereIn('cartons.area', $allArea);
                        }
                    })
                    ->where(function ($q) use ($oRequest) {
                        $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
                        $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : null;
                        if ($dateInit !== null || $dateEnd !== null) {
                            return $q
                                ->wherebetween('cartons.created_at', [$dateInit, $dateEnd]);
                        }
                    })
                    ->where(
                        function ($q) use ($sFiltro) {
                            if ($sFiltro !== false) {
                                return $q
                                    ->orWhere('cartons.waveNumber', 'like', "%$sFiltro%")
                                    ->orWhere('cartons.wave_id', '=', $sFiltro)
                                    ->orWhere('cartons.store', '=', $sFiltro)
                                    ->orWhere('cartons.barcode', 'like', "%$sFiltro%");
                            }
                        }
                    )
                    ->where(
                        function ($q) use ($oRequest) {
                            $style = ($oRequest->style) ? $oRequest->style : null;
                            $sku = ($oRequest->sku) ? $oRequest->sku : null;
                            if ($style !== null || $sku !== null) {
                                return $q
                                    ->orWhere('carton_lines.sku', $sku)
                                    ->orWhere('carton_lines.style', $style);
                            }
                        }
                    )
                    ->orderBy('cartons.updated_at', 'desc')
                    ->get();
                $result = collect($aCartons);
                if (isset($oRequest->orderBy)) {
                    if ($oRequest->orderDirection == 'asc') {
                        $result = $result->sortBy($oRequest->orderBy);
                    } else {
                        $result = $result->sortByDesc($oRequest->orderBy);
                    }
                }
                $result = $result->paginate((int) $oRequest->input('per_page'));
                return $result;
            }

            $aParseCarton = [];
            foreach ($aCartons as $aCarton) {
                $aCarton->labelDetail = json_decode($aCarton->labelDetail);
                $aParseCarton[] = $aCarton;
            }
            $aCartons->data = $aParseCarton;

            return response()->json(["cartons" =>  $aCartons], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Carton',
                'message' => 'Error al obtener el recurso: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * @param $oRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCartonsInOnePage($oRequest)
    {
        try {
            $oValidator = Validator::make($oRequest->all(), [
                'order' => 'max:30|in:id',
                'search' => 'max:100',
                'extension' => 'between:1,999',
                'sort' => 'in:asc,desc',
            ]);
            if ($oValidator->fails()) {
                return response()->json(["status" => "fail", "message" => json_encode($oValidator->errors())], 400);
            }
            $aCartons = "";
            $getArea = CartonRepository::AREA[$oRequest->area];
            $sFiltro = $oRequest->input('search', false);
            $style = ($oRequest->style) ? $oRequest->style : null;
            $sku = ($oRequest->sku) ? $oRequest->sku : null;
            $wave = ($oRequest->wave) ? $oRequest->wave : null;
            if ($oRequest->dateInit !== null && $oRequest->dateEnd !== null && $oRequest->area == 5) {
                $aCartons = $this->mCarton
                    ->whereNull('transferStatus')
                    ->whereNull('transferNum')
                    ->orderBy('cartons.updated_at', 'desc')
                    ->get();
            } elseif ($oRequest->dateInit !== null && $oRequest->dateEnd !== null && $sku == null && $style == null) {
                $aCartons = $this->mCarton
                    ->where(function ($q) use ($getArea) {
                        $allArea = CartonRepository::AREA;
                        unset($allArea[4]);
                        unset($allArea[5]);
                        if ($getArea !== CartonRepository::AREA[4]) {
                            return $q->where('area', $getArea);
                        } else {
                            return $q->whereIn('area', $allArea);
                        }
                    })
                    ->where(function ($q) use ($oRequest) {
                        $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
                        $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : null;
                        if ($dateInit !== null || $dateEnd !== null) {
                            return $q
                                ->wherebetween('created_at', [$dateInit, $dateEnd]);
                        }
                    })
                    ->where(function ($q) use ($wave) {
                        if ($wave !== null) {
                            return $q->where('wave_id', $wave);
                        }
                    })
                    ->where(
                        function ($q) use ($sFiltro) {
                            if ($sFiltro !== false) {
                                return $q
                                    ->orWhere('waveNumber', 'like', "%$sFiltro%")
                                    ->orWhere('wave_id', '=', $sFiltro)
                                    ->orWhere('store', '=', $sFiltro)
                                    ->orWhere('barcode', 'like', "%$sFiltro%");
                            }
                        }
                    )
                    ->orderBy('updated_at', 'desc')
                    ->get();
            } elseif ($oRequest->dateInit !== null && $oRequest->dateEnd !== null || $style !== null || $sku !== null) {
                $aCartons = $this->mCarton
                    ->join('carton_lines', 'carton_lines.carton_id', '=', 'cartons.id')
                    ->where(function ($q) use ($getArea) {
                        $allArea = CartonRepository::AREA;
                        unset($allArea[4]);
                        unset($allArea[5]);
                        if ($getArea !== CartonRepository::AREA[4]) {
                            return $q->where('cartons.area', $getArea);
                        } else {
                            return $q->whereIn('cartons.area', $allArea);
                        }
                    })
                    ->where(function ($q) use ($oRequest) {
                        $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
                        $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : null;
                        if ($dateInit !== null || $dateEnd !== null) {
                            return $q
                                ->wherebetween('cartons.created_at', [$dateInit, $dateEnd]);
                        }
                    })
                    ->where(
                        function ($q) use ($sFiltro) {
                            if ($sFiltro !== false) {
                                return $q
                                    ->orWhere('cartons.waveNumber', 'like', "%$sFiltro%")
                                    ->orWhere('cartons.wave_id', '=', $sFiltro)
                                    ->orWhere('cartons.store', '=', $sFiltro)
                                    ->orWhere('cartons.barcode', 'like', "%$sFiltro%");
                            }
                        }
                    )
                    ->where(
                        function ($q) use ($oRequest) {
                            $style = ($oRequest->style) ? $oRequest->style : null;
                            $sku = ($oRequest->sku) ? $oRequest->sku : null;
                            if ($style !== null || $sku !== null) {
                                return $q
                                    ->orWhere('carton_lines.sku', $sku)
                                    ->orWhere('carton_lines.style', $style);
                            }
                        }
                    )
                    ->orderBy('cartons.updated_at', 'desc')
                    ->get();
            }

            $aParseCarton = [];
            foreach ($aCartons as $aCarton) {
                $aCarton->labelDetail = json_decode($aCarton->labelDetail);
                $aParseCarton[] = $aCarton;
            }
            $aCartons->data = $aParseCarton;

            return response()->json(["cartons" =>  $aCartons], 200);
        } catch (\Exception $e) {
            Log::error('Error en ' . __METHOD__ . ' línea ' . $e->getLine() . ':' . $e->getMessage());
            return  response()->json([
                'code' => 500,
                'type' => 'Carton',
                'message' => 'Error al obtener el recurso: ' . $e->getMessage(),
            ]);
        }
    }
    /**
     * Crea una caja.
     *
     * @param  Array  $cartonData
     * @return \Illuminate\Http\Response
     */
    public function createCarton(array $cartonData)
    {
        $wave = Wave::find($cartonData['waveNumber']);
        $order = Order::find($cartonData['orderNumber']);
        $lines = $cartonData['lines'];
        $cartonData['total_pieces'] = 0;
        if (empty($wave)) {
            return ApiResponses::notFound('La Ola especificada no fue encontrada');
        } else {
            $cartonData['wave_id'] = $wave->id;
        }
        if (empty($order)) {
            return ApiResponses::notFound('La Orden especificada no fue encontrada');
        } else {
            $cartonData['order_id'] = $order->id;
        }
        $carton = $this->create($cartonData);
        $total_pieces = $this->cartonLineRepository->createCartonLines($wave->id, $carton->id, $lines);
        $carton->total_pieces = $total_pieces;
        $carton->save();
        return $carton;
    }

    /**
     * Actualiza una caja (proposito general).
     *
     * @param \App\Carton $model    modelo de Carton
     * @param Array     $cartonData datos de caja para actualizar
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCarton($model, array $cartonData)
    {

        if (isset($cartonData['wave_id'])) {
            $wave = Wave::find($cartonData['wave_id']);
            if (empty($wave)) {
                return ApiResponses::notFound('La Ola especificada no fue encontrada');
            }
            $model->wave_id = $cartonData['wave_id'];
        }
        if (isset($cartonData['order_id'])) {
            $order = Order::find($cartonData['order_id']);
            if (empty($order)) {
                return ApiResponses::notFound('La Orden especificada no fue encontrada');
            }
            $model->order_id = $cartonData['order_id'];
        }

        $total_pieces = $this->cartonLineRepository->updateCartonLines($lines);
        $carton->total_pieces = $total_pieces;
        $carton->save();
        return ApiResponses::okObject($carton);
    }

    /**
     * Actualiza una caja con su transferencia.
     *
     * @param  $request peticion
     * @return \Illuminate\Http\JsonResponse
     */
    public function setTransfer(Request $request)
    {
        $carton = Carton::where('barcode', $request->boxId)->first();
        if (empty($carton)) {
            return ApiResponses::okObject(['exito' => false, 'mensaje' => 'BoxId no encontrado']);
        } else {
            if ($request->transferencia == $carton->transferNum) {
                return ApiResponses::okObject(['exito' => true, 'mensaje' => 'Transferencia ya registrada.']);
            }
            $carton->transferNum = $request->transferencia;
            $carton->transferStatus = $carton->transferStatus ?? 1;
            $carton->save();
            return ApiResponses::okObject(['exito' => true, 'mensaje' => 'No. de Transferencia recibido.']);
        }
    }

    /**
     * Actualiza una caja con su embarque.
     *
     * @param  $request peticion
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function setShipment(Request $request)
    {
        $rBoxes = array_column($request->cajas, 'boxId');
        $cartons = Carton::whereIn('barcode', $rBoxes)->update(['shipment' => $request->embarque]);

        return ApiResponses::okObject(['exito' => true, 'mensaje' => 'No. de embarque recibido.', 'incidencias' => []]);
    }

    /**
     * @param $barcode
     * @return bool|object
     */
    public function getZplCarton($barcode = null, $carton = null)
    {
        if (empty($carton) && $barcode !== null) {
            $carton = $this->mCarton->where('barcode', '=', $barcode)->first();
        }
        if ($carton != null) {
            $scanner = $this->rScannerBox->orderRequestInfo($carton);
            $zpl = preg_replace("/\r|\n/", "", $scanner->zpl);
            $url = (object)['url' => "http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/" . $zpl];
            return ApiResponses::okObject($url);
        } else {
            return ApiResponses::okObject('ble');
        }
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Http\Response
     */
    public function getCartonDetail($id)
    {
        $cartonDetail = $this->cartonLineRepository->getLineByCarton($id);
        $result = [];
        foreach ($cartonDetail as $carton) {
            $detail = json_decode($carton->carton->labelDetail);
            $result[] = [
                'division' => $detail->division[0],
                'sku' => $carton->line->sku,
                'style' => $carton->style,
                'category' => $detail->details[0]->category,
                'classification' => $detail->details[0]->classification,
                'pieces' => $carton->pieces,
                'prepacks' => $carton->prepacks,
                'pieces_aud' => $carton->pieces_aud,
                'prepacks_aud' => $carton->prepacks_aud
            ];
        }
        $result = collect($result);

        return ApiResponses::okObject($result);
    }

    /**
     * @param $oRequest
     * @param $waveId
     * @return mixed
     */
    public function getCartonWave($oRequest, $waveId)
    {
        $aCartons = [];
        if ($oRequest->paginate === "false") {
            $aCartons = $this->mCarton
                ->where('wave_id', $waveId)
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $aCartons = $this->mCarton
                ->where('wave_id', $waveId)
                ->orderBy('updated_at', 'desc')
                ->paginate((int) $oRequest->input('per_page', 25));
        }
        return  $aCartons;
    }

    /**
     * @param $oRequest
     */
    public function getAuditList($oRequest)
    {
        $oValidator = Validator::make($oRequest->all(), [
            'per_page' => 'numeric|between:5,1000',
            'order' => 'max:30|in:id',
            'search' => 'max:100',
            'extension' => 'between:1,999',
            'sort' => 'in:asc,desc',
        ]);
        if ($oValidator->fails()) {
            return response()->json(["status" => "fail", "message" => json_encode($oValidator->errors())], 400);
        }
        $sFilter =  $oRequest->input('search', false);
        $status = $oRequest->status;

        $aCartons = $this->mCarton
            ->where(function ($q) use ($oRequest, $sFilter, $status) {
                if ($oRequest->dateInit !== null && $oRequest->dateEnd !== null) {
                    $q->whereBetween('updated_at', [Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00', Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59']);
                } else {
                    $q->where('updated_at', '>', date('Y-m-d'));
                }
                if ($oRequest->wave > 0) {
                    $q->where('wave_id', '=', $oRequest->wave);
                }
                if ($status == 0) {
                    $q->where('transferStatus', 2);
                }
                if ($status != 0) {
                    $q->whereNotNull('audit_end');
                }
            })
            ->where(function ($q) use ($sFilter) {
                if ($sFilter !== false) {
                    return $q
                        ->where('barcode', 'like', "%$sFilter%")
                        ->orWhere('store', '=', "$sFilter");
                }
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $result = collect($aCartons);
        if (isset($oRequest->orderBy)) {
            if ($oRequest->orderDirection == 'asc') {
                $result = $result->sortBy($oRequest->orderBy);
            } else {
                $result = $result->sortByDesc($oRequest->orderBy);
            }
        }
        $result = $result->paginate((int) $oRequest->input('per_page'));
        return $result;
    }

    /**
     * @param $barcode
     */
    public function getCartonContents($barcode)
    {
        $carton = $this->mCarton
            ->where('barcode', $barcode)
            ->with('cartonLines')
            ->first();
        return $carton;
    }

    /**
     * @param $request
     */
    public function auditCarton(Request $request, Int $user = null, String $ip = null)
    {
        try {
            $totalPieces = 0;
            foreach ($request->contents as $key => $con) {
                if (isset($con["id"])) {
                    $cl = CartonLine::find($con["id"]);
                    $cl->prepacks_aud = $con["prepacks_audit"];
                    $cl->pieces_aud = $con["prepacks_audit"] * $cl->line->ppk;
                    $cl->save();
                    $line = $cl->line;
                    if ($cl->carton->pendingConfirmation === 0) {
                        $line->pieces_in_carton = ($line->pieces_in_carton - $cl->pieces) + ($con["prepacks_audit"] * $line->ppk);
                        $line->prepacks_in_carton = ($line->prepacks_in_carton - $cl->prepacks) + $con["prepacks_audit"];
                        $line->save();
                    } else {
                        $line->pieces_in_carton = $line->pieces_in_carton + ($con["prepacks_audit"] * $line->ppk);
                        $line->prepacks_in_carton = $line->prepacks_in_carton + $con["prepacks_audit"];
                        $line->save();
                    }
                } else {
                    $line = Line::find($con["line_id"]);
                    $cl = new CartonLine;
                    $cl->line_id = $con["line_id"];
                    $cl->carton_id = $request->cartonId;
                    $cl->prepacks = 0;
                    $cl->pieces = 0;
                    $cl->prepacks_aud = $con["prepacks_audit"];
                    $cl->pieces_aud = $con["prepacks_audit"] * $line->ppk;
                    $cl->sku = $line->sku;
                    $cl->style = $line->style->style;
                    $cl->save();
                }
                $totalPieces += $cl->pieces_aud;
            }
            $carton = Carton::find($request->cartonId);
            $carton->transferStatus = 3;
            $carton->total_pieces = $totalPieces;
            $carton->pendingConfirmation = 0;
            $carton->audited_by = Auth::id();
            $carton->audit_end = new \DateTime();
            $carton->authorized_by = $user ?? null;
            $carton->save();
            $adminWamas = new AdminWamasFileManager;
            $adminSaalma = new AdminSAALMAManager;
            $register = $adminWamas->getRegisterRequest([$carton]);
            $result = $adminSaalma->registerCartons($register);
            $carton->transferStatus = $result["exito"] === true ? 4 : 3;
            $carton->save();
            if ($ip != null) {
                $printRepo = new ScannerBoxRepository();
                $getInfoScan = $printRepo->getInfoScan(substr($carton->barcode, 2, 6));
                $client = new Client($ip);
                $client->send($getInfoScan->zpl);
            }
            return ["exito" => $result["exito"], "message" => "OMS: El carton ha sido ajustado... SAALMA: " . $result["mensaje"], "variant" => $result["exito"] === true ? "sucess" : "warning"];
        } catch (Exception $e) {
            return ["exito" => false, "message" => "Ha ocurrido un error inesperado, reportelo al administrador.", "variant" => "error"];
        }
    }

    /**
     * @param $transfer
     * @param $init
     * @param null $end
     * @return mixed
     */
    public function getNumberCartons($transfer, $init, $end = null, $chart = false)
    {
        $dateInit = ($init) ? Carbon::parse($init)->format('Y-m-d') . ' 00:00:00' : Carbon::now()->format('Y-m-d') . ' 00:00:00';
        $dateEnd = ($end != null) ? Carbon::parse($end)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
        $cartons = collect([]);
        switch ($transfer) {
            case 1:
                $cartons = $this->mCarton->whereBetween('created_at', [$dateInit, $dateEnd])->get();
                break;
            case 2:
                $cartons = $this->mCarton->whereBetween('created_at', [$dateInit, $dateEnd])->where('transferNum', '!=', null)->get();
                break;
            case 3:
                $cartons = $this->mCarton->whereBetween('created_at', [$dateInit, $dateEnd])->where('transferNum', '=', null)->get();
                break;
        }
        return $cartons;
    }

    /**
     * @param $startWeek
     * @param $endWeek
     * @param $today
     * @return mixed
     */
    public function getCartonStats($startWeek, $endWeek, $today)
    {
        $cartonsToday = Carton::whereBetween('created_at', [$today . ' 00:00:00', $today . ' 23:59:59'])
            ->select(DB::raw('COUNT(barcode) as count'), 'transferStatus', DB::raw('CAST(SUM(total_pieces) as integer) as pieces'))
            ->groupBy('transferStatus')
            ->get();
        $totalNoTransfer = Carton::whereNull('transferNum')
            ->count();

        $withTransfer = 0;
        $withoutTransfer = 0;
        $totalToday = 0;
        $piecesToday = 0;
        foreach ($cartonsToday as $ct) {
            if ($ct->transferStatus === null) {
                $withoutTransfer += $ct->count;
            } else {
                $withTransfer += $ct->count;
            }
            $totalToday += $ct->count;
            $piecesToday += $ct->pieces;
        }
        $tenWeek = Carbon::parse($startWeek)->subWeeks(11)->format('Y-m-d') . ' 00:00:00';
        $cartonsChart = Carton::select(
            DB::raw('YEARWEEK(created_at) as date'),
            DB::raw('COUNT(*) as boxes')
        )
            ->whereBetween('created_at', [$tenWeek, $today . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('boxes', 'date')
            ->toArray();
        $period = new \Carbon\CarbonPeriod($tenWeek, '1 week', $today);
        foreach ($period as $date) {
            if (!isset($cartons[$date->year() . $date->week()])) {
                $cartons[$date->year() . $date->week()] = 0;
            }
        }
        ksort($cartonsChart);
        $cartonStats = [
            'cartonsToday' => $totalToday,
            'cartonsWeek' => end($cartonsChart),
            'cartonsChart' => $cartonsChart,
            'piecesToday' => $piecesToday,
            'withTransfer' => $withTransfer,
            'withoutTransfer' => $withoutTransfer,
            'totalNoTransfer' => $totalNoTransfer
        ];
        return $cartonStats;
    }

    public function getCartons($oRequest)
    {
        try {
            $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : Carbon::now()->format('Y-m-d') . ' 00:00:00';
            $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
            $style = ($oRequest->style) ? $oRequest->style : null;
            $sku = ($oRequest->sku) ? $oRequest->sku : null;
            $wave = ($oRequest->wave) ? $oRequest->wave : null;
            $area = (int)$oRequest->area;
            return $this->mCarton
                ->where(function ($q) use ($area, $dateInit, $dateEnd) {
                    if ($area === 5) {
                        return $q
                            ->whereNull('cartons.transferStatus')
                            ->whereNull('cartons.transferNum');
                    } elseif ($area === 6) {
                        return $q
                            ->whereIn('cartons.transferStatus', [1, 5])
                            ->whereNull('cartons.transferNum');
                    } elseif ($area === 4) {
                        return $q
                            ->whereIn('cartons.area', CartonRepository::AREAS)
                            ->wherebetween('cartons.created_at', [$dateInit, $dateEnd]);
                    } else {
                        return $q
                            ->where('cartons.area', [CartonRepository::AREA[$area]])
                            ->wherebetween('cartons.created_at', [$dateInit, $dateEnd]);
                    }
                })
                ->where(function ($q) use ($style, $sku, $wave) {
                    if ($style !== null || $sku !== null) {
                        $q->whereHas('cartonLines', function ($query) use ($sku, $style) {
                            if ($sku !== null) {
                                $query->where('carton_lines.sku', $sku);
                            }
                            if ($style !== null) {
                                $query->where('carton_lines.style', $style);
                            }
                        });
                    }
                    if ($wave !== null) {
                        $q->where('wave_id', $wave);
                    }
                    return $q;
                })
                ->where(function ($q) use ($oRequest) {
                    $sFilter = $oRequest->input('search', false);
                    if ($sFilter !== false) {
                        return $q
                            ->orWhere('cartons.waveNumber', 'like', "%$sFilter%")
                            ->orWhere('cartons.wave_id', '=', $sFilter)
                            ->orWhere('cartons.transferNum', '=', $sFilter)
                            ->orWhere('cartons.store', '=', $sFilter)
                            ->orWhere('cartons.barcode', 'like', "%$sFilter%");
                    }
                })
                ->select(
                    'cartons.id',
                    'cartons.created_at',
                    'cartons.area',
                    'cartons.barcode',
                    'cartons.wave_id',
                    'cartons.transferNum',
                    'cartons.total_pieces',
                    'cartons.store',
                    'cartons.shipment'
                )->get()->toArray();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getCartonsInWave($request)
    {
        $wave = DB::table('cartons_report')
            ->where('ola', '=', $request->wave)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'GRUPO');
        $sheet->setCellValue('C1', 'O.S.');
        $sheet->setCellValue('D1', 'AREA');
        $sheet->setCellValue('E1', 'OLA');
        $sheet->setCellValue('F1', 'BOXID');
        $sheet->setCellValue('G1', 'TRF');
        $sheet->setCellValue('H1', 'EMBARQUE');
        $sheet->setCellValue('I1', 'DESTINO');
        $sheet->setCellValue('J1', 'SKU');
        $sheet->setCellValue('K1', 'ESTILO');
        $sheet->setCellValue('L1', 'PREPACKS');
        $sheet->setCellValue('M1', 'PIEZAS');
        $sheet->setCellValue('N1', 'PREPACKS AUD.');
        $sheet->setCellValue('O1', 'PIEZAS AUD.');
        $sheet->setCellValue('P1', 'AUDITADO POR');
        $sheet->setCellValue('Q1', 'AUTORIZADO POR');
        $sheet->setCellValue('R1', 'INICIO AUD');
        $sheet->setCellValue('S1', 'FIN AUD');

        $rows = 2;

        foreach ($wave as $key => $carton) {
            $sheet->setCellValue('A' . $rows, (string)$carton->fecha_creacion);
            $sheet->setCellValue('B' . $rows, (int)$carton->grupo);
            $sheet->setCellValue('C' . $rows, (string)$carton->orden_surtido);
            $sheet->setCellValue('D' . $rows, (string)$carton->area);
            $sheet->setCellValue('E' . $rows, (int)$carton->ola);
            $sheet->setCellValue('F' . $rows, (string)$carton->boxId);
            $sheet->setCellValue('G' . $rows, (int)$carton->transferencia);
            $sheet->setCellValue('H' . $rows, $carton->shipment ?? 'CEDIS');
            $sheet->setCellValue('I' . $rows, (int)$carton->tienda);
            $sheet->setCellValue('J' . $rows, (int)$carton->sku);
            $sheet->setCellValue('K' . $rows, (int)$carton->estilo);
            $sheet->setCellValue('L' . $rows, (int)$carton->prepacks);
            $sheet->setCellValue('M' . $rows, (int)$carton->piezas);
            $sheet->setCellValue('N' . $rows, (int)$carton->prepacks_aud);
            $sheet->setCellValue('O' . $rows, (int)$carton->pieces_aud);
            $sheet->setCellValue('P' . $rows, $carton->audited_by == 10000 ? 'WAMAS' : $carton->name);
            $sheet->setCellValue('Q' . $rows, $carton->autoriza);
            $sheet->setCellValue('R' . $rows, (string)$carton->inicio_aud);
            $sheet->setCellValue('S' . $rows, (string)$carton->fin_aud);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $request->wave . '_contenidos.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }
}
