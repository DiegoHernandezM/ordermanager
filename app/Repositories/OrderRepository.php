<?php

namespace App\Repositories;

use App\Http\Controllers\ApiResponses;
use App\Order;
use App\Repositories\LineRepository;
use App\Repositories\OrderGroupRepository;
use App\Store;
use App\OrderGroup;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use ZipStream\Exception;

class OrderRepository extends BaseRepository
{
    protected $model = 'App\Order';
    const WOMEN_DIVISION = 1;
    const MEN_DIVISION = 2;
    const KIDS_DIVISION = 3;
    const UNDERWEAR_DIVISION = 4;
    const ACCESORY_DIVISION = 5;
    protected $orderGroupRepository;

    public function __construct()
    {
        $this->lineRepository = new LineRepository();
        $this->orderGroupRepository = new OrderGroupRepository();
    }

  /**
   * Crea una orden con sus contenidos.
   *
   * @param  Array  $orderData
   * @return \Illuminate\Http\Response
   */
    public function createOrder(Array $orderData, bool $withCounter = null)
    {
        $lines = $orderData['lines'];
        unset($orderData['lines']);
        $incidents = [];
        $response = new \stdClass();
        $store = Store::where('number', $orderData['store'])->first();
        if (!empty($store)) {
            $orderData['store_id'] = $store->id;
            $order = $this->create($orderData);
            $result = $this->lineRepository->createLines($lines, $order->id);
            $response->linesCreated = $result->lines;
            if (count($result->incidents) > 0) {
                $response->incidents['Order '.$order->merc_id][] = $result->incidents;
            }
            $response->orderCreated = 1;
        } else {
            $response->incidents['Tienda'][] = ['Tienda '.$orderData['store'].' no se encontró.'];
            $response->orderCreated = 0;
            $response->linesCreated = 0;
        }
        if ($withCounter === true) {
            return $response;
        }
        return $order;
    }

    /**
     * Obtiene las ordenes por division.
     *
     * @param  Array  $orderData
     * @return \Illuminate\Http\Response
     */
    public function getOrdersFromDivision()
    {
        $orders = Order::with(['lines' => function ($q) {
            $q->select('id', 'pieces', 'prepacks', 'style_id', 'order_id');
            $q->where('wave_id', null); // buscamos solo los que no tengan una ola asignada
            $q->with('style:id,division_id');
            $q->whereHas('style', function ($q) {
            });
        },
            'store:id,name'
        ])
       // ->limit(50) /* QUITAR EL LIMITE AL FINAL!!!!!!!!!!!!!! */
        ->get()
        ->toArray();

        $total_pieces = 0;
        $divisions[1] = 0; // MUJER
        $divisions[2] = 0; // HOMBRE
        $divisions[3] = 0; // KIDS
        $divisions[4] = 0; // INTERIORES
        $divisions[5] = 0; // ACCESORIOS Y CALZADO
        foreach ($orders as $key => $ord) {
            $pieces = 0;
            foreach ($ord['lines'] as $key2 => $ln) {
                $divisions[$ln['style']['division_id']] += $ln['pieces'];
                $pieces += $ln['pieces'];
            }
            unset($orders[$key]['lines']);
            $orders[$key]['order_pieces'] = $pieces;
            $total_pieces += $pieces;
        }
        $orders['total_pieces'] = $total_pieces;
        $orders[] = $divisions;
        return $orders;
    }
    
    /**
   * Actualiza una orden con todo y sus contenidos.
   *
   * @param  $model
   * @param  Array  $orderData
   * @return \Illuminate\Http\Response
   */
    public function updateOrder($model, Array $orderData)
    {
        $lines = $orderData['lines'];
        unset($orderData['lines']);
        $store = Store::where('number', $orderData['store'])->first();
        if (!empty($store)) {
            $orderData['store_id'] = $store->id;
            $order = $this->update($model, $orderData);
            if (count($lines) > 0) {
                $order->lines()->delete();
                $lines = $this->lineRepository->updateLines($order->id, $lines);
            }
        } else {
            return null; // TODO: Tabla de incidencias.
        }
        return $order;
    }

     /**
    * Elimina una orden y sus contenidos.
    *
    * @param  $order
    * @return \Illuminate\Http\Response
    */
    public function deleteOrder($order)
    {
        $order->lines()->delete();
        $order->delete();
        return ApiResponses::ok("Orden eliminada.");
    }

    public function getRulesFromMerc()
    {
        $model = new $this->model;

        return $model::$createFromMercRules;
    }

    /**
     * Fusiona dos ordenes de surtido
     *
     * @param  $oRequest
     * @return \Illuminate\Http\Response
     */
    public function updateSupplyOrders($oRequest)
    {
        try {
            $orderGroupIdOne = (int)$oRequest['supply_order_one'];
            $orderGroupIdTwo = (int)$oRequest['supply_order_two'];

            $countLinesOrderOne = OrderGroup::find($orderGroupIdOne)->lines->count();
            $countLinesOrderTwo = OrderGroup::find($orderGroupIdTwo)->lines->count();

            if ($countLinesOrderOne > $countLinesOrderTwo) {
                $updateSupplyOrders = DB::update('update `lines` l join orders o on o.id = l.order_id set l.order_id = (select id from orders o2 where o2.storeNumber = o.storeNumber and o2.order_group_id = '.$orderGroupIdOne.') where o.order_group_id = '.$orderGroupIdTwo);
                if ($updateSupplyOrders>0) {
                    DB::update('update `order_groups` og set og.statusMerged = 1 where id IN ('.$orderGroupIdOne.','.$orderGroupIdTwo.')');

                    $og_one = OrderGroup::find($orderGroupIdOne);
                    $og_two = OrderGroup::find($orderGroupIdTwo);

                    $this->orderGroupRepository->calculatePiecesForRedis($og_one);
                    $this->orderGroupRepository->calculatePiecesForRedis($og_two);

                    $dataResponse = [
                        'status'  => 200,
                        'message' =>  'Ordenes fusionadas' ?? null
                    ];

                }else {
                    $dataResponse = [
                        'status'  => 400,
                        'message' =>  'Alguna orden no cuenta con lineas de surtido' ?? null
                    ];
                }
                return $dataResponse;
            }else{
                $dataResponse = [
                    'status'  => 400,
                    'message' =>  'No se pudo fusionar estas olas, intente de nuevo' ?? null
                ];
                return $dataResponse;
            }
        }catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }
}
