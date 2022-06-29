<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Jobs\CreateAllocationGroupJob;
use App\Jobs\CreateOrderGroupJob;
use App\Managers\Admin\AdminProductSizeManager;
use App\OrderGroup;
use App\Repositories\OrderGroupRepository;
use App\Repositories\ReportRepository;
use Illuminate\Http\Request;
use App\Log as Logger;

class OrderGroupController extends Controller
{
    public function __construct(Request $request)
    {
        $this->orderGroupRepository = new OrderGroupRepository();
    }

    /**
     * Crea un grupo de ordenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createOrderGroup(Request $request)
    {
        try {
            if (count($request->orders) > 0) {
                CreateOrderGroupJob::dispatch($request->all())->onConnection('redis');
                $response = [];
                $response['status'] = true;
                $response['message'] = 'Received successfully with no errors.';
                $response['incidents'] = [];
            } else {
                $response = [];
                $response['status'] = false;
                $response['message'] = 'Received with errors.';
                $response['incidents'] = ['Empty ordergroup'];
            };

            return ApiResponses::okObject($response);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Crea un grupo de allocations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createAllocationGroup(Request $request)
    {
        try {
            $log = Logger::create([
                'message' => json_encode($request->all()),
                'loggable_id' => 1,
                'loggable_type' => 'Ordergroups',
                'user_id' => 1
            ]);
            if (count($request->allocations) > 0) {
                $exists = OrderGroup::where('allocationgroup', $request['allocationGroupId'])->first();
                if (!empty($exists)) {
                    return [
                        'success' => false,
                        'message' => 'Ya se ha registrado esta orden de surtido número de agrupación: ' . $request['allocationGroupId']
                    ];
                }
                CreateAllocationGroupJob::dispatch($request->all())->onConnection('redis');
                $response = [];
                $response['status'] = true;
                $response['message'] = 'Received successfully with no errors.';
                $response['incidents'] = [];
            } else {
                $response = [];
                $response['status'] = false;
                $response['message'] = 'Received with errors.';
                $response['incidents'] = ['Empty allocationgroup'];
            }
            return ApiResponses::okObject($response);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene la lista de grupo de ordenes de la semana.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCurrentWeekOrderGroups(Request $request)
    {
        try {
            $result = $this->orderGroupRepository->getCurrentWeekOrderGroups($request);
            return ApiResponses::okObject($result);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene la lista de lineas del grupo de orden actual.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLinesFiltered(Request $request)
    {
        try {
            $result = $this->orderGroupRepository->getLinesFiltered($request->order_group, $request->division);
            return ApiResponses::okObject($result);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Exporta a excel la auditoria de un grupo de ordenes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getOrderGroupExcel(Request $request)
    {
        try {
            return $this->orderGroupRepository->getExcel($request->order_group);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene la lista de rutas con sus ordenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getRoutesWithOrders(Request $request)
    {
        try {
            $result = $this->orderGroupRepository->getRoutesWithOrders($request->wave_id);
            return ApiResponses::okObject($result);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene la lista de lineas de determinado sku y grupo de ordenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getOrderGroupSkuDetail(Request $request)
    {
        try {
            $result = $this->orderGroupRepository->getOrderGroupSkuDetail($request->order_group, $request->sku, $request->style, $request->provider);
            return ApiResponses::okObject($result);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene los conteos de departamentos de cada orden
     * @param $orderGroupId
     * @return \Illuminate\Http\Response
     */
    public function getDeparmentsOrder(Request $oRequest, $orderGroupId)
    {
        try {
            $result = $this->orderGroupRepository->getOrdersDeparment($orderGroupId, $oRequest->division);
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Obtiene conteo de skus por estilo de cada orden de surtido
     * @param $order
     * @return \Illuminate\Http\Response
     */
    public function getDetailsOrder(Request $oRequest, $order)
    {
        try {
            $result = $this->orderGroupRepository->getDetailOrder($order);
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function recalculate($id)
    {
        try {
            $og = OrderGroup::find($id);
            if ($og) {
                $recalulate = $this->orderGroupRepository->calculatePiecesForRedis($og);
                return ApiResponses::okObject($recalulate);
            } else {
                return ApiResponses::notFound();
            }
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Exporta a excel informacion de ordenes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDataOrderGroup(Request $request)
    {
        try {
            return $this->orderGroupRepository->getInfoExcel($request, true);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|void
     */
    public function getStoresByOrderGroup(Request $request)
    {
        try {
            $stores =  $this->orderGroupRepository->getStores($request);
            return ApiResponses::okObject($stores);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrderStore(Request $request)
    {
        try {
            $order = $this->orderGroupRepository->updateStoreInOrder($request);
            return ApiResponses::okObject($order);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function updateOrderInStore(Request $request)
    {
        try {
            $order = $this->orderGroupRepository->updateStoreOrder($request);
            return ApiResponses::okObject($order);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function updateOrderSlots(Request $request)
    {
        try {
            $order = $this->orderGroupRepository->updateSlotsWave($request);
            return ApiResponses::okObject($order);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $request
     */
    public function getCartonsReport(Request $request)
    {
        try {
            return $this->orderGroupRepository->getCartonsInOrderGroup($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * @param Request $request
     */
    public function getOrderGroupSummary(Request $request)
    {
        try {
            return $this->orderGroupRepository->getOrderGroupSummary($request);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
