<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Jobs\ProcessOrderGroupJob;
use App\Managers\Admin\AdminOrderManager;
use App\Order;
use App\Repositories\OrderRepository;
use App\Variation;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{

    public function __construct(Request $request)
    {
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Obtiene la lista de ordenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        return $this->orderRepository->paginate($request->per_page);
    }
    /**
     * Guarda una orden nueva.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(OrderStoreRequest $request)
    {
        try {
            $result = $this->orderRepository->createOrder($request->all(), true);
            return ApiResponses::okObject($result);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
    /**
     * Guarda muchas ordenes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createMany(Request $request)
    {
        $lineCounter = 0;
        $orderCounter = 0;
        $incidents = [];
        $response = new \stdClass();
        foreach ($request->orders as $key => $order) {
            $v = Validator::make($order, $this->orderRepository->getRules());
            if ($v->fails()) {
                return ApiResponses::badRequest();
            }
            try {
                $result = $this->orderRepository->createOrder($order, true);
                $orderCounter += $result->orderCreated;
                $lineCounter += $result->linesCreated;
                if (isset($result->incidents)) {
                    $incidents[] = $result->incidents;
                }
                // enviando true, nos debe devolver cuantas lineas creo en la orden;
            } catch (\Exception $e) {
                return ApiResponses::internalServerError($e);
            }
        }
        $response->lines = $lineCounter;
        $response->orders = $orderCounter;
        $response->incidents = $incidents;
        return ApiResponses::okObject($response);
    }
    /**
     * Guarda muchas ordenes al estilo de mercaderias.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createManyFromMerc(Request $request)
    {
        $lineCounter = 0;
        $orderCounter = 0;
        $incidents = [];
        $response = new \stdClass();
        
        foreach ($request->orders as $key => $order) {
            $v = Validator::make($order, $this->orderRepository->getRules());
            if ($v->fails()) {
                return ApiResponses::badRequest();
            }
            try {
                $result = $this->orderRepository->createOrder($order, true);
                $orderCounter += $result->orderCreated;
                $lineCounter += $result->linesCreated;
                if (isset($result->incidents)) {
                    $incidents[] = $result->incidents;
                }
                // enviando true, nos debe devolver cuantas lineas creo en la orden;
            } catch (\Exception $e) {
                return ApiResponses::internalServerError($e);
            }
        }
        $response->lines = $lineCounter;
        $response->orders = $orderCounter;
        $response->incidents = $incidents;
        return ApiResponses::okObject($response);
    }
    /**
     * Edita una orden.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $orderId)
    {
        $v = Validator::make($request->all(), $this->orderRepository->getUpdateRules());
        if ($v->fails()) {
            return ApiResponses::badRequest();
        }
        try {
            $order = Order::find($orderId);
            if (empty($order)) {
                return ApiResponses::notFound('No se encontró la orden');
            }
            return $this->orderRepository->updateOrder($order, $request->all());
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function importOrders(Request $request)
    {
        // ProcessOrderGroupJob::dispatch('9_dic_resurtido.csv')->onConnection('sqs-fifo');
        $orderManager = new AdminOrderManager();
        $orderManager->processOrderGroup($request->file);
        return ApiResponses::ok();
    }

    /**
     * Find orders by division.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getFromDivision(Request $request)
    {
        try {
            return $this->orderRepository->getOrdersFromDivision();
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request, $orderId)
    {
        try {
            $order = Order::find($orderId);
            if (empty($order)) {
                return ApiResponses::notFound('No se encontró la orden');
            }
            return $this->orderRepository->deleteOrder($order);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    /**
     * Merge supply order
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function mergeSupplyOrders(Request $request)
    {
        try {
            $mergeOrders = $this->orderRepository->updateSupplyOrders($request->all());
            return ApiResponses::okObject($mergeOrders);

        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
