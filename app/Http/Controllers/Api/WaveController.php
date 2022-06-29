<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Jobs\CompleteSupplyJob;
use App\Jobs\InventoryCheckJob;
use App\Managers\Admin\AdminSAALMAManager;
use App\Repositories\CartonRepository;
use App\Repositories\LineRepository;
use App\Repositories\OrderGroupRepository;
use App\Repositories\ReportRepository;
use App\Repositories\WaveRepository;
use App\Wave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Zebra\Client;
use http\Env\Response;
use DB;

class WaveController extends Controller
{
    protected $waveRepository;
    protected $cartonRepository;

    public function __construct(Request $request)
    {
        $this->waveRepository = new WaveRepository();
        $this->cartonRepository = new CartonRepository();
        $this->orderGroupRepository = new OrderGroupRepository();
    }
    /**
     * Obtiene la lista de olas.
     *
     * @param \Illuminate\Http\Request $request paginado
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        return $this->waveRepository->getAll($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAllForPicking(Request $request)
    {
        return $this->waveRepository->getAllForPicking($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCurrentPicking(Request $request)
    {
        return $this->waveRepository->getCurrentPicking($request);
    }

    public function getProgressDepartments($waveId)
    {
        return $this->waveRepository->getProgressDepartments($waveId);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAllForFinished(Request $request)
    {
        return $this->waveRepository->getAllForFinished($request);
    }

    public function getDetailsWaveRef(Request $request)
    {
        if (!isset($request->page)) {
            $reportRepository = new ReportRepository;
            return $reportRepository->getPalletsReport($request->wave_id);
        }
        return $this->waveRepository->getDetailsWaveRef($request->wave_id, $request->department_id, $request->size);
    }

    /**
     * Obtiene una ola con sus ordenes.
     *
     * @param integer $idWave
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        return $this->waveRepository->getWave($request->id);
    }

    /**
     * Obtiene una ola con sus ordenes en formato Json adecuado para WAMAS.
     *
     * @param integer $idWave
     *
     * @return \Illuminate\Http\Response
     */
    public function getJson(Request $request)
    {
        return $this->waveRepository->getJson($request->id, $request->area, $request->maxslots);
    }

    /**
     * Obtiene una ola de devolucion con sus ordenes en formato Json adecuado para WAMAS.
     *
     * @param integer $idWave
     *
     * @return \Illuminate\Http\Response
     */
    public function getJsonDevolution(Request $request)
    {
        return $this->waveRepository->getJsonDevolution($request->id, $request->area);
    }

    /**
     * Obtiene una ola de devolucion desde un archivo de excel.
     *
     * @param integer $idWave
     *
     * @return \Illuminate\Http\Response
     */
    public function getJsonXls(Request $request)
    {
        if ($request->hasFile('excel')) {
            return $this->waveRepository->getJsonXls($request->excel, $request->area, $request->waveName);
        } else {
            return ApiResponses::badRequest('Se requiere el archivo de excel para generar ola de devolución.');
        }
    }

    /**
     * Obtiene OLAS en picking, pickeadas o en sorter.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInPicking()
    {
        $waves = Wave::whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
        ->select('id', 'planned_pieces', 'stock_pieces', 'picked_pieces', 'picked_boxes', 'total_sku', 'available_skus')
        ->with(['pickedSkus' => function ($q) {
            $q->select('wave_id', DB::raw('count(distinct(sku)) as skus'))->groupBy('wave_id');
        }])
        ->with(['pallets' => function ($q) {
            $q->select(
                'wave_id',
                DB::raw('SUM(IF(status in (1, 3, 4), 1, 0)) as received_bins'),
                DB::raw('count(*) as total_bins')
            )->groupBy('wave_id');
        }])->get();
        return ApiResponses::okObject($waves);
    }

    /**
     * Hace un pre-calculo de piezas, skus y tiendas para los parametros de la ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function precalculate(Request $request)
    {
        $v = Validator::make($request->all(), $this->waveRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            return $this->waveRepository->precalculateWave($request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Guarda una ola nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $v = Validator::make($request->all(), $this->waveRepository->getRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            return $this->waveRepository->createWaveNew($request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Edita una ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $waveId)
    {
        $v = Validator::make($request->all(), $this->waveRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            return $this->waveRepository->updateWave($wave, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Completa el surtido de una ola y hace los ajustes requeridos al inventario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function completeSupply(Request $request)
    {
        $v = Validator::make($request->all(), Wave::$completeSupplyRules);
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $wave = Wave::find($request->codigoOla);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            if ($wave->complete == 1) {
                return ApiResponses::okObject(['exito' => true, 'mensaje' => 'La ola ya se ha cerrado.']);
            }
            // $lineRepository = new LineRepository;
            // $result = $lineRepository->adjustQuantitiesBySupply($wave);
            CompleteSupplyJob::dispatch($wave->id)->onConnection('redis');

            return ApiResponses::okObject(['exito' => true, 'mensaje' => 'La notificación se recibió correctamente.']);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Borra una ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            return $this->waveRepository->delete($waveId);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Consulta en SAALMA las transferencias de devolución.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function queryDevolutionTransfers(Request $request)
    {
        try {
            $saalmaManager = new AdminSAALMAManager();
            $query = http_build_query($request->all());
            $transferStock = $saalmaManager->getDevolutionTransfers($query);
            $transfers = [];
            $skus = [];
            $total_pieces = 0;
            foreach ($transferStock['items'] as $key => $item) {
                $skus[$item['sku']] = 1;
                $transfers[$item['transferencia']]= 1;
                $total_pieces += $item['totalPiezas'];
            }
            $total_transfers = count($transfers);
            $total_sku = count($skus);
            $result = ['data' => $transferStock['items'], 'total_transfers' => $total_transfers, 'total_sku' => $total_sku, 'total_pieces' => $total_pieces, 'updated_at' => date('Y/m/d h:i:a')];
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Consulta en SAALMA las transferencias de devolución.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function makeDevolutionWave(Request $request)
    {
        try {
            $transferList = $request->transferList;
            $orders = $this->waveRepository->makeDevolutionWave($transferList);
            return ApiResponses::okObject($orders);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Verifica inventario en SAALMA y NO hace ajustes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function onlyCheckStock(Request $request, $waveId)
    {
        $wave = Wave::find($waveId);
        if (empty($wave)) {
            return ApiResponses::notFound('No se encontró la ola');
        }
        $saalmaManager = new AdminSAALMAManager();
        $lineRepository = new LineRepository();
        $inventoryRequest = [];
        if ($wave->ordergroup->local == '10110') {
            $almacen = '20';
        } else {
            $almacen = '20';
        }
        $inventoryRequest['almacen'] = $almacen;
        $skuList = [];
        $lines = $lineRepository->waveLinesSumBySku($wave);
        foreach ($lines as $key => $ln) {
            $skuList[] = $ln['sku'];
        }
        $inventoryRequest['skuList'] = $skuList;
        $actualStock = $saalmaManager->getInventoryDev($inventoryRequest);
        return ApiResponses::okObject($actualStock);
    }

    /**
     * Verifica inventario en SAALMA y hace ajustes necesarios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function verifyStock(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            if ($wave->verified_stock == 0) {
                $wave->verified_stock = 2;
                $wave->save();
                // InventoryCheckJob::dispatch($waveId)->onConnection('redis');
                $saalmaManager = new AdminSAALMAManager();
                $lineRepository = new LineRepository();
                $inventoryRequest = [];
                if ($wave->ordergroup->local == '10110') {
                    $almacen = '20';
                } else {
                    $almacen = '20';
                }
                $inventoryRequest['almacen'] = $almacen;
                $skuList = [];
                $lines = $lineRepository->waveLinesSumBySku($wave);
                foreach ($lines as $key => $ln) {
                    $skuList[] = $ln['sku'];
                }
                $inventoryRequest['skuList'] = $skuList;
                $actualStock = $saalmaManager->getInventory($inventoryRequest);
                $incidents = $lineRepository->adjustQuantitiesByPriority(
                    $waveId,
                    $lines,
                    $actualStock
                );
                $wave->pieces = $wave->lines()->sum('expected_pieces');
                $wave->prepacks = $wave->lines()->sum('prepacks');
                $wave->available_skus = $wave->lines()->distinct('sku')->where('expected_pieces', '>', 0)->count();
                $wave->stock_pieces = $wave->pieces;
                if (count($incidents) > 0) {
                    $wave->verified_stock = 3;
                } else {
                    $wave->verified_stock = 1;
                }
                $wave->save();
                return ApiResponses::okObject($incidents);
            }
            if ($wave->verified_stock == 1) {
                $lineRepository = new LineRepository();
                $lines = $lineRepository->waveLinesSumBySku($wave);
                $total_ask   = 0;
                $total_stock = 0;
                $total_diff  = 0;
                $total_prepacks = 0;
                $styleCounter = 0;
                $result = [];
                foreach ($lines as $key => $ln) {
                    if ($key == 0) {
                        $styleCounter = 1;
                    } elseif ($ln['style_id'] !== $lines[$key-1]['style_id']) {
                        $styleCounter++;
                    }
                    $lines[$key]['styleCounter'] = $styleCounter;
                    $total_ask += $ln['ask_pieces'];
                    $total_stock += $ln['in_stock'];
                    $total_prepacks += $ln['prepacks'];
                    $total_diff += $ln['difference'];
                }
                $updated = new \DateTime($wave->updated_at);
                $result['lines'] = $lines;
                $result['updated_at'] = $updated->format('Y/m/d h:i a');
                $result['initial_stock'] = $wave->stock_pieces;
                $result['total_ask'] = $total_ask;
                $result['total_stock'] = $total_stock;
                $result['total_prepacks'] = $total_prepacks;
                $result['total_diff'] = $total_diff;
                $result['percentage_available'] = round(($total_stock/$total_ask)*100, 2);
                $result['percentage_initial'] = round(($wave->stock_pieces/$total_ask)*100, 2);
                $result['statusWave'] = $wave->status;
                if ($wave->order_group_id == 0) {
                    $result['devolution'] = true;
                }
                return ApiResponses::okObject($result);
            } if ($wave->verified_stock == 3) {
                $lineRepository = new LineRepository();
                $unresolvedSkus = $lineRepository->getUnresolvedWaveSkus($wave);
                return ApiResponses::okObject($unresolvedSkus);
            } else {
                return ApiResponses::okObject(['message' => 'La ola ya se encuentra en proceso de verificación de inventario.']);
            }

            //return $this->waveRepository->register($wave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Verifica inventario en SAALMA PRUEBAS y hace ajustes necesarios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function verifyStockDev(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            if ($wave->verified_stock == 0) {
                //$wave->verified_stock = 2;
                $wave->save();
                // InventoryCheckJob::dispatch($waveId)->onConnection('redis');
                $saalmaManager = new AdminSAALMAManager();
                $lineRepository = new LineRepository();
                $inventoryRequest = [];
                if ($wave->ordergroup->local == '10110') {
                    $almacen = '20';
                } else {
                    $almacen = '20';
                }
                $inventoryRequest['almacen'] = $almacen;
                $skuList = [];
                $lines = $lineRepository->waveLinesSumBySku($wave);
                foreach ($lines as $key => $ln) {
                    $skuList[] = $ln['sku'];
                }
                $inventoryRequest['skuList'] = $skuList;
                $actualStock = $saalmaManager->getInventoryDev($inventoryRequest);
                $adjustedLines = $lineRepository->adjustQuantitiesByPriority(
                    $waveId,
                    $lines,
                    $actualStock
                );
                $wave->pieces = $wave->lines()->sum('expected_pieces');
                $wave->prepacks = $wave->lines()->sum('prepacks');
                $wave->available_skus = $wave->lines()->distinct('sku')->where('expected_pieces', '>', 0)->count();
                $wave->verified_stock = 1;
                $wave->save();
                return ApiResponses::okObject(['message' => 'Iniciando check de inventario.']);
            }
            if ($wave->verified_stock == 1) {
                $lineRepository = new LineRepository();
                $lines = $lineRepository->waveLinesSumBySku($wave);
                $total_ask   = 0;
                $total_stock = 0;
                $total_diff  = 0;
                $total_prepacks = 0;
                $result = [];
                foreach ($lines as $key => $ln) {
                    $total_ask += $ln['ask_pieces'];
                    $total_stock += $ln['in_stock'];
                    $total_prepacks += $ln['prepacks'];
                    $total_diff += $ln['difference'];
                }
                $updated = new \DateTime($wave->updated_at);
                $result['lines'] = $lines;
                $result['updated_at'] = $updated->format('Y/m/d h:i a');
                $result['total_ask'] = $total_ask;
                $result['total_stock'] = $total_stock;
                $result['total_prepacks'] = $total_prepacks;
                $result['total_diff'] = $total_diff;
                $result['percentage_available'] = round(($total_stock/$total_ask)*100, 2);
                return ApiResponses::okObject($result);
            } else {
                return ApiResponses::okObject(['message' => 'La ola ya se encuentra en proceso de verificación de inventario.']);
            }

            //return $this->waveRepository->register($wave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Actualiza el ppk de las lines en la ola de los SKU definidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function updateWaveLinesPpk(Request $request, $waveId)
    {
        $lineRepository = new LineRepository();
        $saalmaManager = new AdminSAALMAManager();
        $wave = Wave::find($waveId);
        $lines = $lineRepository->updateWaveLinesPpk($request, $waveId);
        $inventoryRequest = [];
        $inventoryRequest['skuList'] = array_column($request->skuArray, 'sku');
        $inventoryRequest['almacen'] = 20;

        $actualStock = $saalmaManager->getInventory($inventoryRequest);
        $adjustedLines = $lineRepository->adjustQuantitiesByPriority($waveId, $lines, $actualStock, true);
        $wave->pieces = $wave->lines()->sum('expected_pieces');
        $wave->prepacks = $wave->lines()->sum('prepacks');
        $wave->verified_stock = 1;
        $wave->save();
        return ApiResponses::okObject($adjustedLines);
    }

    /**
     * Vuelve a hacer la verificacion del inventario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function verifyStockAgain(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            if ($wave->status >= 3) {
                return ApiResponses::badRequest('Ya no se puede recalcular en este estado de ola.');
            }
            $saalmaManager = new AdminSAALMAManager();
            $lineRepository = new LineRepository();
            $inventoryRequest = [];
            if ($wave->ordergroup->local == '10110') {
                $almacen = '20';
            } else {
                $almacen = '20';
            }
            $inventoryRequest['almacen'] = $almacen;
            $skuList = [];
            $lines = $lineRepository->waveLinesSumBySku($wave);
            foreach ($lines as $key => $ln) {
                $skuList[] = $ln['sku'];
            }
            $inventoryRequest['skuList'] = $skuList;
            $actualStock = $saalmaManager->getInventory($inventoryRequest);
            $adjustedLines = $lineRepository->adjustQuantitiesByPriority(
                $waveId,
                $lines,
                $actualStock,
                true
            );
            $wave->pieces = $wave->lines()->sum('expected_pieces');
            $wave->prepacks = $wave->lines()->sum('prepacks');
            $wave->available_skus = $wave->lines()->distinct('sku')->where('expected_pieces', '>', 0)->count();
            $wave->stock_pieces = $wave->pieces;
            $wave->save();
            return ApiResponses::okObject(['message' => 'Iniciando check de inventario.']);
            //return $this->waveRepository->register($wave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getSkuLines(Request $request)
    {
        try {
            $wave = Wave::find($request->waveId);
            $lineRepository = new LineRepository();
            $lines = $lineRepository->waveLinesSku($wave, $request->variation);
            return ApiResponses::okObject($lines);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Registra una ola en SAALMA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if ($wave->status !== Wave::PICKING && $wave->verified_stock == 1) {
                $saalma = new AdminSAALMAManager();
                $registerWaveRequest = $this->waveRepository->getRegisterRequest($waveId);
                $result = $saalma->registerWave($registerWaveRequest);
                if ($result['mensaje'] == "La Ola registro correctamente") {
                    $wave->status = Wave::PICKING;
                    $wave->save();
                } elseif (strpos($result["mensaje"], (string)$wave->id))  {
                    $wave->status = Wave::PICKING;
                    $wave->save();
                }
                return ApiResponses::okObject($result);
            } else {
                return ApiResponses::okObject(['message' => 'La ola ya se encuentra registrada en SAALMA.']);
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Registra una ola en SAALMA PRUEBAS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function registerDev(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if ($wave->status !== Wave::PICKING && $wave->verified_stock == 1) {
                $saalma = new AdminSAALMAManager();
                $registerWaveRequest = $this->waveRepository->getRegisterRequest($waveId);
                $result = $saalma->registerWaveDev($registerWaveRequest);
                if ($result['mensaje'] == "La Ola registro correctamente") {
                    $wave->status = Wave::PICKING;
                    $wave->save();
                }
                return ApiResponses::okObject($result);
            } else {
                return ApiResponses::okObject(['message' => 'La ola ya se encuentra registrada en SAALMA.']);
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Cancela una ola.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function cancel(Request $request, $waveId)
    {
        try {
            $wave = Wave::find($waveId);
            if (empty($wave)) {
                return ApiResponses::notFound('No se encontró la ola');
            }
            return $this->waveRepository->cancelWave($wave, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function test()
    {
        return $this->waveRepository->test();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAllInSorting(Request $request)
    {
        return $this->waveRepository->getAllInSorter($request);
    }

    /**
     * @param Request $oRequest
     * @return \Illuminate\Http\Response
     */
    public function getDataDashboard(Request $oRequest)
    {
        try {
            $carbon = new Carbon();
            $startOfWeek = $carbon->startOfWeek()->toDateTimeString();
            $endOfWeek = $carbon->endOfWeek()->toDateTimeString();
            $today = $carbon->now()->toDateTimeString();

            $wavesToday = $this->waveRepository->getAllWaveFinished($today);
            $wavesWeek = $this->waveRepository->getAllWaveFinished($startOfWeek, $endOfWeek);
            $cartonsToday = $this->cartonRepository->getNumberCartons(1, $today);
            $cartonsWithTransfer = $this->cartonRepository->getNumberCartons(2, $today);
            $cartonsWithoutTransfer = $this->cartonRepository->getNumberCartons(3, $today);
            $cartonsWeek = $this->cartonRepository->getNumberCartons(2, $startOfWeek, $endOfWeek);
            $piecesToday = 0;
            $piecesWeek = 0;

            foreach ($cartonsToday as $cartonToday) {
                $piecesToday += $cartonToday->total_pieces;
            }
            foreach ($cartonsWeek as $cartonWeek) {
                $piecesWeek += $cartonWeek->total_pieces;
            }

            $dataDashboard = [
                'wavesToday' => count($wavesToday),
                'wavesWeek' => count($wavesWeek),
                'cartonsToday' => count($cartonsToday),
                'cartonsWeek' => count($cartonsWeek),
                'piecesToday' => $piecesToday,
                'piecesWeek' => $piecesWeek,
                'cartonsWithTransfer' => count($cartonsWithTransfer),
                'cartonsWithoutTransfer' => count($cartonsWithoutTransfer),
            ];

            return ApiResponses::okObject($dataDashboard);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function getDataDashboardNew()
    {
        try {
            $carbon = new Carbon();
            $startOfWeek = $carbon->startOfWeek()->format('Y-m-d');
            $endOfWeek = $carbon->endOfWeek()->format('Y-m-d');
            $today = $carbon->now()->format('Y-m-d');
            $waves = $this->waveRepository->getWaveStats($startOfWeek, $endOfWeek, $today);
            $cartons = $this->cartonRepository->getCartonStats($startOfWeek, $endOfWeek, $today);
            $orders = $this->orderGroupRepository->getOrdersStats($startOfWeek, $endOfWeek, $today);
            $dataDashboard = [
                'waves' => $waves,
                'cartons' => $cartons,
                'orders'  => $orders
            ];
            return ApiResponses::okObject($dataDashboard);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function getDepartmenWave($wave)
    {
        try {
            $departments = $this->waveRepository->getDepartmentsByWave($wave);
            return ApiResponses::okObject($departments);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * compara skus en inventario.
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function checkSkuInventory(Request $oRequest)
    {
        try {
            return $this->waveRepository->getExcelWithSkusIntoInventory($oRequest->data);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * verifica la contraseña del usuario
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function verifyPassword(Request $oRequest)
    {
        try {
            $validatePassword = $this->waveRepository->checkPassword($oRequest->all());
            return ApiResponses::okObject($validatePassword);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * obtiene los diferentes destinos, sumatoria de piezas y salidas por destino de una ola
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function getDestinations(Request $oRequest, $waveId)
    {
        try {
            $destinations = $this->waveRepository->getDestinations($waveId);
            return ApiResponses::okObject($destinations);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * obtiene los diferentes destinos, sumatoria de piezas y salidas por destino de una ola
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function updateSlots(Request $oRequest)
    {
        try {
            return $this->waveRepository->updateWaveSlots($oRequest);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Cancela una ola
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function cancelWave(Request $oRequest)
    {
        try {
            $cancelWave = $this->waveRepository->cancelWaveInPicking($oRequest->all());
            return ApiResponses::okObject($cancelWave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Cancela una ola (nuevo front)
     *
     * @param  \Illuminate\Http\Request  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function cancelWaveNew(Request $oRequest)
    {
        try {
            $cancelWave = $this->waveRepository->cancelWaveNew($oRequest->all());
            return ApiResponses::okObject($cancelWave);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Forzar la finalizacion de ola
     * @param Request $oRequest
     * @return \Illuminate\Http\Response
     */
    public function forceFinishWave(Request $oRequest)
    {
        try {
            $finishWave = $this->waveRepository->finishWave($oRequest);
            if ($finishWave) {
                return ApiResponses::ok('Se realizó el termino de ola');
            } else {
                return ApiResponses::serviceUnavailable();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
