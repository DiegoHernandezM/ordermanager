<?php

namespace App\Http\Controllers\Api;

use App\Carton;
use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Line;
use App\Managers\Admin\AdminSAALMAManager;
use App\Managers\Admin\AdminWamasFileManager;
use App\Repositories\CartonRepository;
use App\Repositories\ReportRepository;
use App\Store;
use App\User;
use App\Wave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;

class CartonController extends Controller
{
    protected $mStore;
    protected $cReports;

    public function __construct(Request $request)
    {
        $this->cartonRepository = new CartonRepository();
        $this->mStore = new Store();
        $this->cReports = new ReportRepository();
    }

    /**
     * Obtiene la lista de cajas.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $oRequest)
    {
        try {
            return $this->cartonRepository->getAllCartons($oRequest);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getAllCartons(Request $oRequest)
    {
        try {
            return $this->cartonRepository->getCartons($oRequest);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Obtiene la lista de cajas sin paginar.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function getAllInOne(Request $oRequest)
    {
        try {
            return $this->cartonRepository->getAllCartonsInOnePage($oRequest);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Guarda una caja nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $v = Validator::make($request->all(), $this->cartonRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest($v->errors());
        }
        try {
            return $this->cartonRepository->createCarton($request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Edita una caja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $cartonId)
    {
        $v = Validator::make($request->all(), $this->cartonRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $carton = Carton::find($cartonId);
            if (empty($carton)) {
                return ApiResponses::notFound('No se encontró la caja');
            }
            return $this->cartonRepository->updateCarton($carton, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Borra una caja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        try {
            if ($request->has('carton_id')) {
                $carton = Carton::find($request->carton_id);
                if (empty($carton)) {
                    return ApiResponses::notFound('No se encontró la caja');
                }
                return $this->cartonRepository->delete($request->carton_id);
            } else {
                return ApiResponses::badRequest();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Busca cajas de una ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findCartons(Request $request)
    {
        try {
            $wave = Wave::find($request->wave_id);
            $productRules = [];
            if ($wave->business_rules) {
                $rules = explode('|', $wave->business_rules);
                foreach ($rules as $key => $rule) {
                    $productRules[] = explode(',', $rule);
                }
            }
            return $this->cartonRepository->findByRules($productRules, $wave->pieces);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Http\Response|object
     */
    public function getZpl($id)
    {
        try {
            $v = Validator::make(['id' => $id], [
                'id' => 'required'
            ]);
            if ($v->fails()) {
                return ApiResponses::badRequest($v->errors());
            }
            $carton = Carton::find($id);
            return $this->cartonRepository->getZplCarton(null, $carton);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function setTransfer(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'boxId' => 'required',
                'transferencia' => 'required',
                'fechaTransfer' => 'required'
            ]);
            if ($v->fails()) {
                return ApiResponses::badRequest($v->errors());
            }
            return $this->cartonRepository->setTransfer($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function setShipment(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'embarque' => 'required',
                'fechaEmb' => 'required',
                'cajas' => 'required'
            ]);
            if ($v->fails()) {
                return ApiResponses::badRequest($v->errors());
            }
            return $this->cartonRepository->setShipment($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function demo(Request $request)
    {
        try {
            if ($request->has('wave')) {
                $adminWamas = new AdminWamasFileManager;
                $cartons = $adminWamas->demoCartons($request->wave);
                // if ($cartons !== false) {
                //     $adminSaalma = new AdminSAALMAManager;
                //     $result = $adminSaalma->registerCartons($cartons);
                // }
            }
            return ApiResponses::okObject($cartons);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function resendCartons(Request $request)
    {
        try {
            if ($request->has('wave')) {
                $cartons = Carton::where('wave_id', $request->wave)
                    ->whereNull('transferNum')
                    ->whereNull('transferStatus')
                    ->groupBy('barcode')
                    ->orderBy('id')
                    ->limit(100)
                    ->get();
                $date = new \DateTime();
                $cartonList = [];
                foreach ($cartons as $key => $carton) {
                    $registerCarton = [];
                    $cartonLines = $carton->cartonLines;
                    $depId = $cartonLines[0]->line->department_id;
                    $department = \App\Department::find($depId)->name;
                    $registerCarton['allocationId'] = (int)$carton->order->allocation ?? null;
                    $registerCarton['boxId'] = $carton->barcode;
                    $registerCarton['fechaCaja'] = $date->format('Y-m-d\TH:i:s');
                    $registerCarton['destino'] = $carton->store;
                    $registerCarton['departamento'] = substr($department, 0, 3);
                    $registerCarton['olaID'] = $request->wave;
                    $registerCarton['detalleCaja'] = [];
                    foreach ($cartonLines as $key => $ln) {
                        $registerCartonDetail = [];
                        $registerCartonDetail['sku'] = $ln->line->sku;
                        $registerCartonDetail['cantidadPzasCaja'] = $ln->pieces;
                        $registerCarton['detalleCaja'][] = $registerCartonDetail;
                    }
                    $cartonList[] = $registerCarton;
                }
                if ($cartonList !== false) {
                    $adminSaalma = new AdminSAALMAManager;
                    $result = $adminSaalma->registerCartons($cartonList);
                    if ($result['exito'] == true) {
                        foreach ($cartons as $c) {
                            $c->transferStatus = 1;
                            $c->save();
                        }
                    }
                }
            } elseif ($request->has('boxId')) {
                $date = new \DateTime();
                $carton = Carton::where('barcode', $request->boxId)->first();
                if ($carton->pendingConfirmation == true) {
                    $result = [];
                    $result['exito'] = false;
                    $result['mensaje'] = "La caja tiene confirmaciones pendientes y no puede ser registrada aún";
                    return ApiResponses::okObject($result);
                }
                $cartonList = [];
                $cartonLines = $carton->cartonLines;
                $depId = $cartonLines[0]->line->department_id;
                $department = \App\Department::find($depId)->name;
                $registerCarton = [];
                $registerCarton['allocationId'] = (int)$carton->order->allocation ?? null;
                $registerCarton['boxId'] = $carton->barcode;
                $registerCarton['fechaCaja'] = $date->format('Y-m-d\TH:i:s');
                $registerCarton['destino'] = $carton->store;
                $registerCarton['departamento'] = substr($department, 0, 3);
                $registerCarton['olaID'] = $carton->waveNumber;
                $registerCarton['detalleCaja'] = [];
                $cartonLines = $carton->cartonLines;
                foreach ($cartonLines as $key => $ln) {
                    $registerCartonDetail = [];
                    $registerCartonDetail['sku'] = $ln->line->sku;
                    $registerCartonDetail['cantidadPzasCaja'] = $ln->pieces;
                    $registerCarton['detalleCaja'][] = $registerCartonDetail;
                }
                $cartonList[] = $registerCarton;
                if ($cartonList !== false) {
                    $adminSaalma = new AdminSAALMAManager;
                    $result = $adminSaalma->registerCartons($cartonList);
                    if ($result['exito'] == true) {
                        $carton->transferStatus = 1;
                        $carton->save();
                    } else {
                        if (strpos($result["mensaje"], $carton->barcode) !== false) {
                            $carton->transferStatus = 1;
                            $carton->save();
                        }
                    }
                }
                if ($result['exito'] == true) {
                    $carton->transferStatus = 1;
                    $carton->save();
                } else {
                    if (strpos($result["mensaje"], $carton->barcode)) {
                        $carton->transferStatus = 1;
                        $carton->save();
                    }
                }
            }
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function manualSync(Request $request)
    {
        try {
            $adminWamas = new AdminWamasFileManager;
            $adminWamas->syncFiles();
            return ApiResponses::ok();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return bool|\Illuminate\Http\Response
     */
    public function getDetailCarton($id)
    {
        try {
            $v = Validator::make(['id' => $id], [
                'id' => 'required|numeric'
            ]);
            if ($v->fails()) {
                return ApiResponses::badRequest($v->errors());
            }
            return $this->cartonRepository->getCartonDetail($id);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function getDetailCartonShipmentStore($id)
    {
        try {
            $stores = $this->mStore->with(['cartons' => function ($q) use ($id) {
                return $q->where('wave_id', $id)->select('shipment')->distinct();
            }])
                ->whereHas('cartons', function ($q) use ($id) {
                    return $q->where('cartons.wave_id', $id);
                })->get();
            return ApiResponses::okObject($stores);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getReportShipment($wave)
    {
        try {
            $stores = $this->mStore->with(['cartons' => function ($q) use ($wave) {
                return $q->where('wave_id', $wave)->select('shipment')->distinct();
            }])
                ->whereHas('cartons', function ($q) use ($wave) {
                    return $q->where('cartons.wave_id', $wave);
                })->get();
            $cartons = $this->cartonRepository->getCartonWave(null, $wave, false);
            return $this->cReports->getReportShipmentWaveStores($stores, $cartons);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $wave
     */
    public function getCartonsWave(Request $request, $wave)
    {
        try {
            return ApiResponses::okObject($this->cartonRepository->getCartonWave($request, $wave));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param String $barcode
     */
    public function initAudit($barcode)
    {
        try {
            if (strlen($barcode) > 6) {
                $carton = Carton::where('barcode', $barcode)->with('cartonLines')->first();
            } else {
                $carton = Carton::where('barcode', 'like', 'C-' . $barcode . '%')
                    ->with('cartonLines')
                    ->orderByDesc('id')
                    ->first();
            }

            if (!empty($carton)) {
                $exito = 'warning';
                if ($carton->transferStatus == 1 || $carton->transferStatus == 5) {
                    $message = 'La caja ya se ha registrado en SAALMA y ya no puede ser modificada por esta aplicación.';
                } elseif ($carton->transferStatus == 2) {
                    $exito = 'audit';
                    $message = 'Comenzando auditoria...';
                } elseif ($carton->transferStatus == 3 || $carton->transferStatus == 4) {
                    $message = 'La caja ya fue auditada y ya no puede ser modificada por esta aplicación.';
                } else {
                    $exito = 'success';
                    $message = 'Reteniendo caja...';
                    $carton->transferStatus = 2;
                    $carton->audit_init = new \DateTime();
                    $carton->save();
                }
            } else {
                $exito = 'error';
                $message = 'No se encontró el boxId';
            }
            return ApiResponses::okObject(['exito' => $exito, 'message' => $message, 'carton' => $carton]);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param String $barcode
     */
    public function endAudit($barcode)
    {
        try {
            if (strlen($barcode) > 6) {
                $carton = Carton::where('barcode', $barcode)->first();
            } else {
                $carton = Carton::where('barcode', 'like', 'C-' . $barcode . '%')
                    ->orderByDesc('id')
                    ->first();
            }

            if (!empty($carton)) {
                $exito = 'warning';
                if ($carton->transferStatus == 1) {
                    $message = 'La caja ya se ha registrado en SAALMA y ya no puede ser modificada por esta aplicación.';
                } elseif ($carton->transferStatus == 2) {
                    $exito = 'success';
                    $message = 'Liberando caja...';
                    $carton->transferStatus = null;
                    $carton->created_at = new \DateTime();
                    $carton->audit_end = new \DateTime();
                    $carton->save();
                } elseif ($carton->pendingConfirmation == true) {
                    $message = 'La caja tiene confirmaciones pendientes del sorter, espere a que sea verificada.';
                } elseif ($carton->transferStatus > 2) {
                    $message = 'La caja ya fue auditada y liberada.';
                } else {
                    $exito = 'false';
                    $message = 'La caja no está retenida';
                }
            } else {
                $exito = 'error';
                $message = 'No se encontró el boxId';
            }
            return ApiResponses::okObject(['exito' => $exito, 'message' => $message, 'carton' => $carton]);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     */
    public function auditList(Request $request)
    {
        try {
            if ($request->report === 'true') {
                $reportRepository = new \App\Repositories\ReportRepository;
                return $reportRepository->getReportAuditedCartons($request);
            }
            return ApiResponses::okObject($this->cartonRepository->getAuditList($request));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     */
    public function auditCarton(Request $request)
    {
        try {
            if ($request->password) {
                $result = $this->checkAuth($request->password);
                if ($result['status'] === true) {
                    return $result;
                }
            }
            return ApiResponses::okObject($this->cartonRepository->auditCarton($request, $result["user"] ?? null, $request->ipAddress));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     */
    public function checkAuditPass(Request $request)
    {
        try {
            $result = $this->checkAuth($request->password);
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     */
    public function findLineSku(Request $request)
    {
        try {
            $line = Line::where([
                'sku' => $request->sku,
                'order_id' => $request->order_id,
                'wave_id' => $request->wave_id
            ])
                ->whereRaw('pieces >= (' . (int)$request->prepacks . ' * ppk)')
                ->first();
            if (!empty($line)) {
                return ApiResponses::okObject(['exito' => true, 'line' => $line->id, 'ppk' => $line->ppk]);
            } else {
                return ApiResponses::okObject(['exito' => false]);
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }


    /**
     * @param String $barcode
     */
    public function cartonContents($barcode)
    {
        try {
            return ApiResponses::okObject($this->cartonRepository->getCartonContents($barcode));
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param $password
     * @return $dataResponse
     */
    public function checkAuth($password)
    {
        $status = true;
        $auditors = User::role('auditor')->get();
        $user = null;
        foreach ($auditors as $auditor) {
            if (Hash::check($password, $auditor->password)) {
                $status = false;
                $user = $auditor->id;
                break;
            }
        }

        if ($status == false) {
            return $dataResponse = [
                'status'    => false,
                'message'   => 'Contraseña correcta',
                'user'      => $user
            ];
        } else {
            return $dataResponse = [
                'status'    => true,
                'message'   => 'Contraseña incorrecta',
                'user'      => $user
            ];
        }
    }

    public function getCartonsContentWave(Request $request)
    {
        try {
            return $this->cartonRepository->getCartonsInWave($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
