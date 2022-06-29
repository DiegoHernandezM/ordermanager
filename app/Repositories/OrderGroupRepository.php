<?php

namespace App\Repositories;

use App\CartonLine;
use App\Department;
use App\Http\Controllers\ApiResponses;
use App\Line;
use App\Order;
use App\OrderGroup;
use App\Repositories\LineRepository;
use App\Route;
use App\Store;
use App\Variation;
use App\Wave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use http\Env\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderGroupRepository extends BaseRepository
{
    protected $model = 'App\OrderGroup';

    /**
     * @param $request
     * @return array
     */
    public function getCurrentWeekOrderGroups(Request $request)
    {
        $orderGroups = OrderGroup::where(
            function ($q) use ($request) {
                if ($request->dateInit !== null || $request->dateEnd !== null) {
                    $q->wherebetween('created_at', [Carbon::parse($request->dateInit)->format('Y-m-d') . ' 00:00:00', Carbon::parse($request->dateEnd)->format('Y-m-d') . ' 23:59:59']);
                }
            }
        );
        if ($request->style !== null) {
            $orderGroups->whereHas('orders', function ($q) use ($request) {
                $q->whereHas('lines', function ($q) use ($request) {
                    $q->whereHas('style', function ($q) use ($request) {
                        $q->where('style', $request->style);
                    });
                });
            });
        }
        if ($request->paginated === 'false') {
            $orderGroups = $orderGroups->orderByDesc('id')->limit(10)->get();
        } else {
            $orderGroups = $orderGroups->orderByDesc('id')->limit(20)->get();
        }

        $result = [];

        foreach ($orderGroups as $key2 => $og) {
            $redisOgPieces =  Redis::get('ordergroups:' . $og->id . ':total_pieces') ?? -1;
            if ($redisOgPieces < 0) {
                $result[] = $this->calculatePiecesForRedis($og);
            } else {
                $divisions = json_decode(Redis::get('ordergroups:' . $og->id . ':divisions'));
                $divisionsParse = [];
                foreach ($divisions as $division) {
                    $division->order_group = $og->id;
                    $divisionsParse[] = $division;
                }
                $result[] = [
                    'created_at'     => $og->created_at,
                    'order_group_id' => $og->id,
                    'order_group'    => $og->description,
                    'reference'      => $og->reference,
                    'local'          => $og->local,
                    'statusMerged'   => $og->statusMerged,
                    'total_pieces'   => (int)Redis::get('ordergroups:' . $og->id . ':total_pieces'),
                    'total_in_wave'  => (int)Redis::get('ordergroups:' . $og->id . ':total_in_wave'),
                    'total_pending'  => (int)Redis::get('ordergroups:' . $og->id . ':total_pending'),
                    'sorted_pieces'  => (int)Redis::get('ordergroups:' . $og->id . ':sorted_pieces'),
                    'divisions'      => $divisions
                ];
            }
        }
        if ($request->paginated === 'true') {
            $result = collect($result);
            if (isset($request->orderBy)) {
                if ($request->orderDirection == 'asc') {
                    $result = $result->sortBy($request->orderBy);
                } else {
                    $result = $result->sortByDesc($request->orderBy);
                }
            }
            $result = $result->paginate((int) $request->size);
        }
        return $result;
    }

    public function calculatePiecesForRedis($og)
    {
        $divisions = [
            1 => 'mujer',
            2 => 'hombre',
            3 => 'kids y bebés',
            4 => 'interiores',
            5 => 'accesorios'
        ];
        $orders = Order::where('order_group_id', $og->id)->where('store_id', '!=', 0)->get();
        $orderGroupTotalPieces = 0;
        $orderGroupTotalInWave = 0;
        $orderGroupTotalPending = 0;
        $orderGroupTotalSorted = 0;
        $totals = [
            'mujer' => [
                'pieces' => 0,
                'lower' => 0,
                'in_wave' => 0,
                'pending' => 0,
            ],
            'hombre' => [
                'pieces' => 0,
                'lower' => 0,
                'in_wave' => 0,
                'pending' => 0,
            ],
            'kids y bebés' => [
                'pieces' => 0,
                'lower' => 0,
                'in_wave' => 0,
                'pending' => 0,
            ],
            'interiores' => [
                'pieces' => 0,
                'lower' => 0,
                'in_wave' => 0,
                'pending' => 0,
            ],
            'accesorios' => [
                'pieces' => 0,
                'lower' => 0,
                'in_wave' => 0,
                'pending' => 0,
            ]
        ];
        foreach ($orders as $key => $value) {
            for ($i = 1; $i < 6; $i++) {
                switch ($i) {
                    case 3: // kids y bebes
                        $divIds = [3, 4];
                        break;
                    case 4: // interiores
                        $divIds = [5];
                        break;
                    case 5: // accesorios y calzado
                        $divIds = [6, 7, 8];
                        break;
                    default:
                        $divIds = [$i];
                        break;
                }
                $pzs = $value->lines()->where(['wave_id' => null])->whereIn('division_id', $divIds)->sum('pieces');
                $lowerPzs = $value->lines()->join('styles', 'lines.style_id', '=', 'styles.id')->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id')->where(['product_classifications.id' => 12])->sum('pieces');
                $pzsInWave = $value->lines()->whereNotNull('wave_id')->whereIn('division_id', $divIds)->sum('pieces');
                $pzsSorted = $value->lines()->whereNotNull('wave_id')->whereIn('division_id', $divIds)->sum('pieces_in_carton');
                $totalPieces = $pzs + $pzsInWave;
                $totals[$divisions[$i]]['pieces'] += $totalPieces;
                $totals[$divisions[$i]]['lower'] += $lowerPzs;
                $totals[$divisions[$i]]['in_wave'] += $pzsInWave;
                $totals[$divisions[$i]]['pending'] += $pzs;
                $orderGroupTotalPieces += $totalPieces;
                $orderGroupTotalInWave += $pzsInWave;
                $orderGroupTotalPending += $pzs;
                $orderGroupTotalSorted += $pzsSorted;
            }
        }
        $tempDivisions = [];
        $count = 1;
        foreach ($totals as $key => $total) {
            $tempDivisions[] = [
                'id'       => $count++,
                'division' => $key,
                'pieces'   => $total['pieces'],
                'lower'   => $total['lower'],
                'in_wave'  => $total['in_wave'],
                'pending'  => $total['pending'],
            ];
        }
        $result = [
            'created_at'     => $og->created_at,
            'order_group_id' => $og->id,
            'order_group'    => $og->description,
            'local'          => $og->local,
            'statusMerged'   => $og->statusMerged,
            'reference'      => $og->reference,
            'total_pieces'   => $orderGroupTotalPieces,
            'total_in_wave'  => $orderGroupTotalInWave,
            'total_pending'  => $orderGroupTotalPending,
            'sorted_pieces'  => $orderGroupTotalSorted,
            'divisions'      => $tempDivisions
        ];
        Redis::set('ordergroups:' . $og->id . ':total_pieces', $orderGroupTotalPieces);
        Redis::set('ordergroups:' . $og->id . ':total_in_wave', $orderGroupTotalInWave);
        Redis::set('ordergroups:' . $og->id . ':total_pending', $orderGroupTotalPending);
        Redis::set('ordergroups:' . $og->id . ':divisions', json_encode($tempDivisions));
        Redis::set('ordergroups:' . $og->id . ':sorted_pieces', $orderGroupTotalSorted);

        return $result;
    }

    /**
     * @param $orderGroup
     * @param $division
     * @return array
     */
    public function getOrdersDeparment($orderGroup, $division)
    {
        $divIds = $this->getRealDivision($division);
        $departments =
            Department::select('id', 'name')
            ->whereIn('division_id', $divIds)
            ->get()
            ->toArray();
        $deptIds = [];
        foreach ($departments as $key => $dept) {
            $deptIds[] = $dept['id'];
        }
        $lines = Line::select('styles.department_id', DB::raw('CAST(SUM(pieces) as SIGNED) as pieces'))
            ->join('styles', 'lines.style_id', '=', 'styles.id')
            ->groupBy('styles.department_id')
            ->whereHas('order', function ($q) use ($orderGroup) {
                $q->where('order_group_id', $orderGroup);
                $q->where('store_id', '!=', 0);
            })
            ->whereHas('style', function ($q) use ($deptIds) {
                $q->whereIn('department_id', $deptIds);
            })->get()->toArray();

        $lines2 = Line::select('styles.department_id', DB::raw('CAST(SUM(pieces) AS SIGNED) as in_wave'))
            ->join('styles', 'lines.style_id', '=', 'styles.id')
            ->whereNotNull('wave_id')
            ->groupBy('styles.department_id')
            ->whereHas('order', function ($q) use ($orderGroup) {
                $q->where('order_group_id', $orderGroup);
                $q->where('store_id', '!=', 0);
            })
            ->whereHas('style', function ($q) use ($deptIds) {
                $q->whereIn('department_id', $deptIds);
            })->get()->toArray();
        $lines3 = Line::select('styles.department_id', DB::raw('CAST(SUM(pieces) AS SIGNED) as lower_parts'))
            ->join('styles', 'lines.style_id', '=', 'styles.id')
            ->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id')
            ->groupBy('styles.department_id')
            ->whereHas('order', function ($q) use ($orderGroup) {
                $q->where('order_group_id', $orderGroup);
                $q->where('store_id', '!=', 0);
            })
            ->whereHas('style', function ($q) use ($deptIds) {
                $q->whereIn('department_id', $deptIds);
            })
            ->where('product_classifications.id', 12)
            ->get()->toArray();
        $allDepartments = [];
        foreach ($departments as $key => $dept) {
            $result = [];
            $totalOffset = array_search($dept['id'], array_column($lines, 'department_id'));
            $total = $totalOffset !== false ? $lines[$totalOffset]['pieces'] : 0;
            $inWaveOffSet = array_search($dept['id'], array_column($lines2, 'department_id'));
            $inWave = $inWaveOffSet !== false ? $lines2[$inWaveOffSet]['in_wave'] : 0;
            $lowerPartsOffSet = array_search($dept['id'], array_column($lines3, 'department_id'));
            $lowerParts = $lowerPartsOffSet !== false ? $lines3[$lowerPartsOffSet]['lower_parts'] : 0;
            if ($total > 0 || $inWave > 0) {
                $pending = $total - $inWave;
                $result['department_name'] = $dept['name'];
                $result['total_pieces'] = $total;
                $result['total_in_wave'] = $inWave;
                $result['total_pending'] = $pending;
                $result['total_lower'] = $lowerParts;
                $allDepartments[] = $result;
            }
        }
        return $allDepartments;
    }

    /**
     * Crea una orden con sus contenidos al estilo de mercaderias.
     *
     * @param  Array  $orderData
     * @return \Illuminate\Http\Response
     */
    public function createOrderFromMerc(array $request)
    {
        $orders = [];
        $incidents = [];
        $v = Validator::make($request, OrderGroup::$orderGroupRulesFromMerc);
        if ($v->fails()) {
            return ApiResponses::badRequest($v->errors());
        }
        $exists = OrderGroup::where('reference', $request['reference'])->first();
        if (!empty($exists)) {
            return [
                'success' => false,
                'message' => 'Ya se ha registrado esta orden de surtido con referencia: ' . $request['reference']
            ];
        }
        foreach ($request['orders'] as $key => $line) {
            $line = (object) $line;
            $sku = $line->sku;
            $department = (int)Redis::get('sku:' . $sku . ':department');
            $storeNumber = $line->store;
            $line->pieces = $line->pieces % $line->ppk == 0 ? $line->pieces : ($line->ppk * $line->prepacks);

            $orders[$storeNumber]['lines'][$sku] = [
                'merc_id'          => $line->id,
                'sku'              => $sku,
                'style_id'         => (int)Redis::get('sku:' . $sku . ':style'),
                'pieces'           => $line->pieces,
                'ppk'              => $line->ppk,
                'department_id'    => $department,
                'division_id'      => Redis::get('stdp:' . $storeNumber . ':' . $department) ? 10 : (int)Redis::get('sku:' . $sku . ':division'),
                'variation_id'     => (int)Redis::get('sku:' . $sku . ':id'),
                'prepacks'         => $line->prepacks,
                'expected_pieces'  => $line->pieces,
                'priority'         => (int)Redis::get('stores:' . $storeNumber . ':ranking')
            ];
        }
        $orderGroup = new OrderGroup;
        $today = new \DateTime();
        $maxId = OrderGroup::whereDate('created_at', Carbon::today())->count();
        $orderGroup->description = 'OS-' . $today->format('ymd') . '-' . ($maxId ? $maxId + 1 : 1);
        $orderGroup->reference = $request['reference'];
        $orderGroup->allocation = $request['allocation'] ?? null;
        $orderGroup->transferencia = $request['transferencia'] ?? null;
        $orderGroup->local = $request['local'];
        $orderGroup->claveOS = $request['claveOS'] ?? null;
        $orderGroup->solicitudId = $request['solicitudId'] ?? null;
        $orderGroup->save();

        $count = 0;
        foreach ($orders as $key => $ord) {
            $order = new Order;
            $store = Store::where('number', $key)->where('status', '!=', 0)->first();
            $order->store_id = !empty($store) ? $store->id : 0;
            $order->storePriority = !empty($store) ? $store->ranking : 999;
            $order->storePosition = !empty($store) ? $store->sorter_ranking : 999;
            $order->routePriority = 1;
            $order->routeNumber = !empty($store) ? $store->route_id : 1;
            $order->storeNumber = !empty($store) ? $store->number : $key;
            $order->routeDescription = !empty($store) ? $store->route->description : '';
            $order->storeDescription = !empty($store) ? $store->name : '';
            $order->status = 1;
            $order->order_group_id = $orderGroup->id;
            $order->slots = 1;
            $order->save();
            $order->lines()->createMany($ord['lines']);
            unset($orders[$key]);
            unset($ord['lines']);
            unset($order);
        }
        $response = $this->calculatePiecesForRedis($orderGroup);
    }

    /**
     * Crea una orden con sus contenidos al estilo de ALLOCATIONS.
     *
     * @param  Array  $orderData
     * @return \Illuminate\Http\Response
     */
    public function createOrderFromAlloc(array $request)
    {
        $v = Validator::make($request, OrderGroup::$orderGroupRulesFromAlloc);
        if ($v->fails()) {
            return ApiResponses::badRequest($v->errors());
        }

        $orderGroup = new OrderGroup;
        $today = new \DateTime();
        $maxId = OrderGroup::whereDate('created_at', Carbon::today())->count();
        $orderGroup->description = 'OS-' . $today->format('ymd') . '-' . ($maxId ? $maxId + 1 : 1);
        $orderGroup->reference = $request['reference'] ?? "Allocation " . $request['allocationGroupId'];
        $orderGroup->allocationgroup = $request['allocationGroupId'] ?? null;
        $orderGroup->local = $request['local'];
        $orderGroup->claveOS = $request['claveOS'] ?? null;
        $orderGroup->solicitudId = $request['solicitudId'] ?? null;
        $orderGroup->save();

        foreach ($request['allocations'] as $key => $alloc) {

            $order = new Order;
            $store = Store::where('number', $alloc["store"])->where('status', '!=', 0)->first();
            $order->allocation = $alloc["allocationId"] ?? null;
            $order->store_id = !empty($store) ? $store->id : 0;
            $order->storePriority = !empty($store) ? $store->ranking : 999;
            $order->storePosition = !empty($store) ? $store->sorter_ranking : 999;
            $order->routePriority = 1;
            $order->routeNumber = !empty($store) ? $store->route_id : 1;
            $order->storeNumber = !empty($store) ? $store->number : $key;
            $order->routeDescription = !empty($store) ? $store->route->description : '';
            $order->storeDescription = !empty($store) ? $store->name : '';
            $order->status = 1;
            $order->order_group_id = $orderGroup->id;
            $order->slots = 1;
            $order->save();

            $contents = [];
            $priority = (int)Redis::get('stores:' . $alloc["store"] . ':ranking');
            foreach ($alloc['contents'] as $key => $ln) {
                $line = (object) $ln;
                $sku = $line->sku;
                $department = (int)Redis::get('sku:' . $sku . ':department');
                $line->pieces = $line->pieces % $line->ppk == 0 ? $line->pieces : ($line->ppk * $line->prepacks);

                $contents[$sku] = [
                    'sku'              => $sku,
                    'style_id'         => (int)Redis::get('sku:' . $sku . ':style'),
                    'pieces'           => $line->pieces,
                    'ppk'              => $line->ppk,
                    'department_id'    => $department,
                    'division_id'      => Redis::get('stdp:' . $alloc["store"] . ':' . $department) ? 10 : (int)Redis::get('sku:' . $sku . ':division'),
                    'variation_id'     => (int)Redis::get('sku:' . $sku . ':id'),
                    'prepacks'         => $line->prepacks,
                    'expected_pieces'  => $line->pieces,
                    'priority'         => $priority
                ];
            }

            $order->lines()->createMany($contents);
        }
        $this->calculatePiecesForRedis($orderGroup);
    }

    public function getLinesFiltered($order_group, $division)
    {
        $orders = Order::where('order_group_id', $order_group)->get();
        $result = [];
        foreach ($orders as $key => $ord) {
            $pieces = $ord->lines()->where('division_id', $division)->sum('pieces');
            $currentOrder = $ord->toArray();
            $currentOrder['pieces'] = (int)$pieces;
            $currentOrder['store'] = $ord->store ? $ord->store->name : '';
            $currentOrder['storeNumber'] = $ord->store ? $ord->store->number : '';
            $currentOrder['storeRoute'] = $ord->store ? $ord->store->route->name : '';
            $result[$key] = $currentOrder;
        }
        return $result;
    }

    public function getExcel($order_group)
    {
        $og = Line::select('lines.wave_id', 'styles.style', 'product_providers.jdaId as provider', 'departments.name', 'lines.sku', 'orders.storeNumber', 'lines.pieces', 'lines.pieces_in_carton')
            ->join('orders', 'lines.order_id', '=', 'orders.id')
            ->join('styles', 'lines.style_id', '=', 'styles.id')
            ->join('product_providers', 'product_providers.id', '=', 'styles.provider_id')
            ->join('departments', 'lines.department_id', '=', 'departments.id')
            ->where('orders.order_group_id', $order_group)
            ->where('orders.store_id', '!=', 0)
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ESTILO');
        $sheet->setCellValue('B1', 'PROVEEDOR');
        $sheet->setCellValue('C1', 'DEPTO');
        $sheet->setCellValue('D1', 'SKU');
        $sheet->setCellValue('E1', 'PIEZAS');
        $sheet->setCellValue('F1', 'SURTIDAS');
        $sheet->setCellValue('G1', 'TIENDA');
        $sheet->setCellValue('H1', 'OLA');

        $rows = 2;

        foreach ($og as $key => $ord) {
            $sheet->setCellValue('A' . $rows, (string)$ord->style);
            $sheet->setCellValue('B' . $rows, (int)$ord->provider);
            $sheet->setCellValue('C' . $rows, (int)$ord->name);
            $sheet->setCellValue('D' . $rows, (int)$ord->sku);
            $sheet->setCellValue('E' . $rows, (int)$ord->pieces);
            $sheet->setCellValue('F' . $rows, (int)$ord->pieces_in_carton);
            $sheet->setCellValue('G' . $rows, (int)$ord->storeNumber);
            $sheet->setCellValue('H' . $rows, (int)$ord->wave_id);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="orden_surtido_' . $order_group . '.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }

    public function getRoutesWithOrders($waveId)
    {
        $routes = Route::get();

        $wave = Wave::find($waveId);
        $bussinesRules = json_decode($wave->business_rules);
        $area = '';

        foreach ($bussinesRules->divisions as $division) {
            $area = ($division > 5) ? 'ptl' : 'sorter';
        }

        $parseOrders = $this->parseOrders($wave, $area, $wave->verify_slots);

        $newWave = Wave::find($waveId);
        $newWave->verify_slots = true;
        $newWave->save();

        $aSlots = json_decode($newWave->order_slots);

        $totalStores = 0;
        foreach ($parseOrders[0] as $key => $parseOrder) {
            foreach ($aSlots as $aSlot) {
                if ($parseOrder->id === $aSlot->order) {
                    $totalStores += 1;
                    $parseOrders[0][$key]->slots = $aSlot->slots;
                }
            }
        }

        $ordersByRoute = [];
        foreach ($parseOrders[0] as $parseOrder) {
            $ordersByRoute[$parseOrder['route']][] = $parseOrder;
        }


        $aRoutesOrders = [];
        foreach ($routes as $route) {
            $sums = (isset($ordersByRoute[$route->id]) > 0) ? $this->sumPieces($ordersByRoute[$route->id]) : 0;
            $aRoutesOrders[] = [
                'id' => $route->id,
                'description' => $route->description,
                'color'  => $route->color,
                'orders' => $ordersByRoute[$route->id] ?? [],
                'pieces' => (isset($ordersByRoute[$route->id]) > 0) ? $sums[0] : 0,
                'stores' => (isset($ordersByRoute[$route->id]) > 0) ? $sums[1] : 0,
            ];
        }

        $routes = [
            'stores'    => $totalStores,
            'slots'     => $parseOrders[1],
            'maxslots'  => ($area === 'ptl') ? 200 : 210,
            'routes'    => $aRoutesOrders,
            'wavestatus' => $wave->status,
            'wave'      => $waveId
        ];

        return $routes;
    }

    /**
     * @param $orders
     * @return int
     */
    public function sumPieces($orders)
    {
        $pieces = 0;
        $stores = 0;
        foreach ($orders as $order) {
            $pieces += $order['sumpzas'];
            if ($order['sumpzas'] > 0) {
                $stores += 1;
            }
        }

        return [$pieces, $stores];
    }

    /**
     * @param $waveId
     * @param $area
     * @param int $maxslots
     * @return mixed
     */
    public function parseOrders($wave, $area, $calculateSlots, $maxslots = 200)
    {
        $limit = ($area === 'ptl') ? 200 : 210;

        $orders = Order::where('order_group_id', $wave->order_group_id)->where('store_id', '>', 0)
            ->whereHas('lines', function ($q) use ($wave) {
                $q->where('expected_pieces', '>', 0);
                $q->where('wave_id', $wave->id);
            })
            ->with(['lines' => function ($q) use ($wave) {
                $q->getBaseQuery()->orders = null;
                $q->where('wave_id', $wave->id);
                $q->where('expected_pieces', '>', 0);
                $q->select(
                    DB::raw('CAST(sum(pieces) as SIGNED) as sumpieces'),
                    DB::raw('CAST(sum(prepacks) as SIGNED) as sumprepacks'),
                    'order_id'
                );
                $q->groupBy('order_id');
            }])->select('id', 'id as orderNumber', 'routeNumber as route', 'storeDescription', 'routePriority', 'storeNumber as store', 'routeDescription', 'storePosition as storePriority', 'slots')
            ->orderBy('storePosition')->get();

        $maxslots = ($maxslots < count($orders)) ? count($orders) : $maxslots;
        $maxslots = ($maxslots > $limit) ? $limit : $maxslots;

        $sumslots = 0;
        $maxppk = 0;
        foreach ($orders as $key => $ord) {
            $sumppks = 0;
            $sumpzas = 0;
            foreach ($ord['lines'] as $key2 => $ln) {
                if ($ln->sumprepacks > 0 || $ln->sumpieces > 0) {
                    $sumppks += $ln->sumprepacks;
                    $maxppk += $ln->sumprepacks;
                    $sumpzas += $ln->sumpieces;
                }
            }
            $orders[$key]->sumppk = $sumppks;
            $orders[$key]->sumpzas = $sumpzas;
            if (count($ord['lines']) > 0) {
                $sumslots += floor($ord->slots);
            }
        }

        if ((bool)$calculateSlots === true) {
            $sumslots = 0;
            $waveSlots = json_decode($wave->order_slots);
            foreach ($waveSlots as $waveSlot) {
                $sumslots += $waveSlot->slots;
            }
        }

        if ((bool)$calculateSlots === false) {
            $slotsOrders = [];
            $sumslots = 0;

            foreach ($orders as $key => $ord) {
                $percent = $ord->sumppk / $maxppk;
                $slots = ($maxslots ?? 190) * $percent;
                if (floor($slots) < 1) {
                    $slots = 1;
                } elseif ($slots > 6) {
                    $slots = 6;
                }

                if (count($ord['lines']) > 0) {
                    $slotsOrders[] = ['order' => $ord->id, 'slots' => (int)$slots];
                    $orders[$key]->slots = (int)$slots;
                    $sumslots += (int)$slots;
                    $slotsByStore[] = [
                        'slots' => $orders[$key]->slots,
                        'store' => $ord->store,
                        'ppk' => $ord->sumppk
                    ];
                }
            }

            if ($maxslots <= count($slotsByStore)) {
                $calculate = $this->calculateSlotsByMaxSlots($maxslots, $sumslots, $slotsByStore, $orders, $wave->id);
                return $calculate;
            }

            $waveRepo = new WaveRepository();
            $recalculateOrders = $waveRepo->calculateSlots($maxslots, $sumslots, $slotsByStore, $orders, true);

            (count($recalculateOrders[2]) > 0) ? $this->saveSlotsWave($wave->id, $recalculateOrders[2]) : $this->saveSlotsWave($wave->id, $slotsOrders);

            return [$recalculateOrders[0], $recalculateOrders[1], count($orders)];
        } else {
            return [$orders, $sumslots, count($orders)];
        }
    }

    public function saveSlotsWave($wave, $orders)
    {
        $wave = Wave::find($wave);
        if (!$wave) {
            return false;
        }
        $wave->order_slots = json_encode($orders);
        $wave->save();
        return true;
    }


    public function calculateSlotsByMaxSlots($maxSlots, $sumslots, $slotsByStore, $orders, $wave)
    {
        usort($slotsByStore, function ($a, $b) {
            if ($a['ppk'] == $b['ppk']) {
                return 0;
            }
            return $a['ppk'] > $b['ppk'] ? 1 : -1;
        });
        $sum = $sumslots;

        while ($maxSlots <= $sum) {
            foreach ($slotsByStore as $key => $slot) {
                if ($maxSlots <= $sum) {
                    if ($slot['slots'] >= 1) {
                        $slotsByStore[$key] = [
                            'slots' => 0,
                            'store' => $slot['store']
                        ];
                        $sum = $sum - 1;
                    }
                } else {
                    break;
                }
            }
        }
        $slotsOrders = [];

        foreach ($orders as $key => $ord) {
            foreach ($slotsByStore as $slot) {
                if ($orders[$key]->store === $slot['store']) {
                    $slotsOrders[] = ['order' => $ord->id, 'slots' => (int)$slot['slots']];
                    $orders[$key]->slots = (int)$slot['slots'];
                }
            }
        }
        $this->saveSlotsWave($wave, $slotsOrders);
        return [$orders, $sum];
    }

    public function updateSlotsWave($request)
    {
        $wave = Wave::find($request->wave);
        if ($wave) {
            $aSlots = json_decode($wave->order_slots);
            foreach ($aSlots as $key => $aSlot) {
                if ($aSlot->order === $request->id) {
                    $aSlots[$key]->slots = ($request->slots <= 0) ? 0 : $request->slots;
                }
            }
            $wave->order_slots = json_encode($aSlots);
            $wave->save();
            return $wave;
        }
    }

    public function getOrderGroupSkuDetail($orderGroupId, $sku, $style, $provider)
    {
        $lines = Line::join('styles', 'lines.style_id', '=', 'styles.id')
            ->with('order:id,storePriority,storeNumber')
            ->whereHas('order', function ($q) use ($orderGroupId) {
                $q->where('order_group_id', $orderGroupId);
            })
            ->where(
                function ($q) use ($sku, $style, $provider) {
                    if ($sku !== false || $style !== false || $provider) {
                        return $q
                            ->orWhere('lines.sku', $sku)
                            ->orWhere('styles.style', $style)
                            ->orWhere('styles.provider_id', $provider);
                    }
                }
            )
            ->select('lines.id', 'lines.sku', 'lines.pieces', 'lines.expected_pieces', 'lines.order_id', 'lines.ppk', 'lines.style_id', 'styles.provider_id', 'styles.style')
            ->get();
        return $lines;
    }

    /**
     * @return array
     */
    private function getRealDivision($division)
    {
        switch ($division) {
            case 3: // kids y bebes
                $divIds = [3, 4];
                break;
            case 4: // interiores
                $divIds = [5];
                break;
            case 5: // accesorios y calzado
                $divIds = [6, 7];
                break;
            default:
                $divIds = [$division];
                break;
        }
        return $divIds;
    }

    /**
     * @param $orderGroup
     * @return mixed
     */
    public function getDetailOrder($orderGroup)
    {
        $lines = Line::join('styles', 'lines.style_id', '=', 'styles.id')
            ->join('orders', 'lines.order_id', '=', 'orders.id')
            ->selectRaw('lines.sku, styles.style, sum(lines.pieces) as sumpieces')
            ->where('orders.order_group_id', $orderGroup)
            ->groupBy('lines.sku')
            ->get();

        return $lines;
    }

    public function getInfoExcel($request, $save = false)
    {
        if ($request->id != null && $request->date == null) {
            $orders = DB::select("select l.sku, s.style, o.storeNumber, l.pieces from `lines` l join styles s on s.id = l.style_id join orders o on o.id = l.order_id where o.order_group_id =" . $request->id);
        } elseif ($request->date != null && $request->id == null) {
            $orders = DB::select("select l.sku, s.style, o.storeNumber, l.pieces from `lines` l join styles s on s.id = l.style_id join orders o on o.id = l.order_id where o.created_at LIKE '%" . $request->date . "%'");
        }

        if (count($orders) > 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'SKU');
            $sheet->setCellValue('B1', 'ESTILO');
            $sheet->setCellValue('C1', 'TIENDA');
            $sheet->setCellValue('D1', 'PIEZAS');

            $rows = 2;
            foreach ($orders as $key => $order) {
                $sheet->setCellValue('A' . $rows, (int)$order->sku);
                $sheet->setCellValue('B' . $rows, (string)$order->style);
                $sheet->setCellValue('C' . $rows, (int)$order->storeNumber);
                $sheet->setCellValue('D' . $rows, (int)$order->pieces);
                $rows++;
            }
            if ($save == false) {
                $response = response()->streamDownload(function () use ($spreadsheet) {
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                });
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $response->headers->set('Content-Disposition', 'attachment; filename="your_file.xls"');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response->send();
            } else {
                $fileNmae = uniqid();
                $writer = new Xlsx($spreadsheet);
                $writer->save(public_path('files/' . $fileNmae . '.xlsx'));
                return $fileNmae;
            }
        } else {
            return false;
        }
    }

    public function getCartonsInOrderGroup($orderGroup)
    {
        $og = DB::table('cartons_report')
            ->where('grupo', '=', $orderGroup->order_group)
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

        foreach ($og as $key => $ord) {
            $sheet->setCellValue('A' . $rows, (string)$ord->fecha_creacion);
            $sheet->setCellValue('B' . $rows, (int)$ord->grupo);
            $sheet->setCellValue('C' . $rows, (string)$ord->orden_surtido);
            $sheet->setCellValue('D' . $rows, (string)$ord->area);
            $sheet->setCellValue('E' . $rows, (int)$ord->ola);
            $sheet->setCellValue('F' . $rows, (string)$ord->boxId);
            $sheet->setCellValue('G' . $rows, (int)$ord->transferencia);
            $sheet->setCellValue('H' . $rows, $ord->shipment ?? 'CEDIS');
            $sheet->setCellValue('I' . $rows, (int)$ord->tienda);
            $sheet->setCellValue('J' . $rows, (int)$ord->sku);
            $sheet->setCellValue('K' . $rows, (int)$ord->estilo);
            $sheet->setCellValue('L' . $rows, (int)$ord->prepacks);
            $sheet->setCellValue('M' . $rows, (int)$ord->piezas);
            $sheet->setCellValue('N' . $rows, (int)$ord->prepacks_aud);
            $sheet->setCellValue('O' . $rows, (int)$ord->pieces_aud);
            $sheet->setCellValue('P' . $rows, $ord->audited_by == 10000 ? 'WAMAS' : $ord->name);
            $sheet->setCellValue('Q' . $rows, $ord->autoriza);
            $sheet->setCellValue('R' . $rows, (string)$ord->inicio_aud);
            $sheet->setCellValue('S' . $rows, (string)$ord->fin_aud);
            $rows++;
        }

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $og[0]->orden_surtido . '_resumen.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }

    public function getOrderGroupSummary($request)
    {
        $og = DB::table('lines')
            ->select(
                'order_groups.reference as orden_surtido',
                'lines.wave_id',
                'styles.style',
                'lines.sku',
                'departments.name',
                DB::raw('sum(pieces) pz_solicitadas'),
                DB::raw('sum(expected_pieces) pz_prorrateadas'),
                DB::raw('IFNULL((select sum(cantidad) from pallet_contents where wave_id = lines.wave_id and sku = lines.sku group by sku), 0) pz_pickeadas'),
                DB::raw('IFNULL((select sum(cajas) from pallet_contents where wave_id = lines.wave_id and sku = lines.sku group by sku), 0) cajas_pickeadas'),
                DB::raw('sum(pieces_in_carton) pz_inducidas')
            )
            ->join('orders', 'orders.id', '=', 'lines.order_id')
            ->join('departments', 'departments.id', '=', 'lines.department_id')
            ->join('order_groups', 'order_groups.id', '=', 'orders.order_group_id')
            ->join('styles', 'styles.id', '=', 'lines.style_id')
            ->whereNotNull('wave_id')
            ->where('orders.order_group_id', $request->order_group)
            ->groupBy('orders.order_group_id', 'lines.wave_id', 'lines.sku')
            ->get();


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'O.S.');
        $sheet->setCellValue('B1', 'Ola');
        $sheet->setCellValue('C1', 'Departamento');
        $sheet->setCellValue('D1', 'Estilo');
        $sheet->setCellValue('E1', 'Sku');
        $sheet->setCellValue('F1', 'Pz Solicitadas');
        $sheet->setCellValue('G1', 'Pz Prorrateadas');
        $sheet->setCellValue('H1', 'Pz Pickeadas');
        $sheet->setCellValue('I1', 'Pz Inducidas');
        $sheet->setCellValue('J1', 'Cajas Pickeadas');

        $rows = 2;

        foreach ($og as $key => $ord) {
            $sheet->setCellValue('A' . $rows, (string)$ord->orden_surtido);
            $sheet->setCellValue('B' . $rows, (int)$ord->wave_id);
            $sheet->setCellValue('C' . $rows, (string)$ord->name);
            $sheet->setCellValue('D' . $rows, (string)$ord->style);
            $sheet->setCellValue('E' . $rows, (string)$ord->sku);
            $sheet->setCellValue('F' . $rows, (int)$ord->pz_solicitadas);
            $sheet->setCellValue('G' . $rows, (int)$ord->pz_prorrateadas);
            $sheet->setCellValue('H' . $rows, (int)$ord->pz_pickeadas);
            $sheet->setCellValue('I' . $rows, (int)$ord->pz_inducidas);
            $sheet->setCellValue('J' . $rows, (int)$ord->cajas_pickeadas);
            $rows++;
        }

        $worksheet1 =  $spreadsheet->createSheet();
        $worksheet1->setTitle('Picking');
        $worksheet1->setCellValue('A1', 'Fecha y Hora');
        $worksheet1->setCellValue('B1', 'Ola');
        $worksheet1->setCellValue('C1', 'Bin');
        $worksheet1->setCellValue('D1', 'Ultimo estado');
        $worksheet1->setCellValue('E1', 'Estilo');
        $worksheet1->setCellValue('F1', 'Departamento');
        $worksheet1->setCellValue('G1', 'Sku');
        $worksheet1->setCellValue('H1', 'Cajas');
        $worksheet1->setCellValue('I1', 'Piezas');
        $worksheet1->setCellValue('J1', 'Ubicó');
        $worksheet1->setCellValue('K1', 'Inducción por');

        $rowsW = 2;

        $dataPicking = DB::table('pallet_contents')
            ->join('departments', 'departments.id', '=', 'pallet_contents.department_id')
            ->join('styles', 'styles.id', '=', 'pallet_contents.style_id')
            ->join('pallets', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->join('waves', 'waves.id', '=', 'pallets.wave_id')
            ->where('waves.order_group_id', $request->order_group)
            ->select(
                'pallet_contents.*',
                'departments.name',
                'styles.style',
                'pallets.lpn_transportador',
                'pallets.status',
                'pallets.assignated_by',
                'pallets.inducted_by'
            )
            ->get();

        foreach ($dataPicking as $pallet) {
            $worksheet1->setCellValue('A' . $rowsW, (string)$pallet->created_at);
            $worksheet1->setCellValue('B' . $rowsW, (int)$pallet->wave_id);
            $worksheet1->setCellValue('C' . $rowsW, (string)$pallet->lpn_transportador);
            $worksheet1->setCellValue('D' . $rowsW, \App\Pallets::STAUS[(int)$pallet->status]);
            $worksheet1->setCellValueExplicit(
                'E' . $rowsW,
                $pallet->style,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $worksheet1->setCellValue('F' . $rowsW, (string)$pallet->name);
            $worksheet1->setCellValueExplicit(
                'G' . $rowsW,
                $pallet->sku,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $worksheet1->setCellValue('H' . $rowsW, (int)$pallet->cajas);
            $worksheet1->setCellValue('I' . $rowsW, (int)$pallet->cantidad);
            $worksheet1->setCellValue('J' . $rowsW, (string)$pallet->assignated_by);
            $worksheet1->setCellValue('K' . $rowsW, (string)$pallet->inducted_by);
            $rowsW++;
        }

        $worksheet1->getColumnDimension('E')->setAutoSize(true);
        $worksheet1->getColumnDimension('G')->setAutoSize(true);

        $response = response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $og[0]->orden_surtido . '_contenidos.xlsx"');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response->send();
    }

    /**
     * @param $request
     * @return mixed
     */
    public function joinOrderGroups($request)
    {
        $orderGroupsIds = $request->ordergroups;
        $orderGroups = OrderGroup::whereIn('id', $orderGroupsIds)->get();
        $ogCount = count($orderGroups);
        $ogDestinationId = $orderGroups[0]->id;
        for ($i = 1; $i < $ogCount; $i++) {
            $lines = $orderGroups[1]->lines;
            foreach ($lines as $key => $ln) {
            }
        }
        return $lines;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getStores($request)
    {
        $orders = Order::where('order_group_id', $request->order_group)
            ->where(
                function ($q) use ($request) {
                    if ($request->has('active')) {
                        return $q
                            ->orWhere('store_id', '>', 0);
                    } else {
                        return $q
                            ->orWhere('orders.store_id', 0);
                    }
                }
            )
            ->join('stores', function ($join) use ($request) {
                if ($request->has('active')) {
                    $join->on('orders.store_id', '=', 'stores.id');
                } else {
                    $join->on('orders.storeNumber', '=', 'stores.number');
                }
            })
            ->select('orders.*', 'stores.name', 'stores.ranking', 'stores.number', 'stores.id as store')->get();

        return $orders;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function updateStoreInOrder($request)
    {
        if ($request->has('desactive')) {
            $order = Order::find($request->id);
            $order->store_id = 0;
            $order->routeDescription = null;
            $order->storeDescription = null;
            $order->storePriority = 999;
            $order->storePosition = 999;
            $order->update();

            $og = OrderGroup::find($order->order_group_id);
            $this->calculatePiecesForRedis($og);

            return $order;
        } else {
            $store = Store::where('number', $request->number)->first();
            $order = Order::find($request->id);
            $order->store_id = $store->id;
            $order->routeDescription = $store->route->description;
            $order->storeDescription = $store->name;
            $order->storePriority = $store->sorter_ranking;
            $order->storePosition = $store->ranking;
            $order->update();

            $og = OrderGroup::find($order->order_group_id);
            $this->calculatePiecesForRedis($og);

            return $order;
        }
    }

    public function updateStoreOrder($request)
    {
        $disabled = $request->disabled === 1 ? true : false;
        $order = Order::find($request->order);
        $store = Store::where('number', $order->storeNumber)->first();
        $order->store_id = $disabled != false ? $store->id : 0;
        $order->routeDescription = $disabled != false ? $store->route->description : null;
        $order->storeDescription = $disabled != false ? $store->name : null;
        $order->storePriority = $disabled != false ? $store->sorter_ranking : 999;
        $order->storePosition = $disabled != false ? $store->ranking : 999;
        $order->update();
        $og = OrderGroup::find($order->order_group_id);
        $this->calculatePiecesForRedis($og);
        return $order;
    }

    public function getOrdersStats($startWeek, $endWeek, $today)
    {
        $tenWeek = Carbon::parse($startWeek)->subWeeks(11)->format('Y-m-d') . ' 00:00:00';
        $orderGroups = OrderGroup::whereBetween('created_at', [$tenWeek, $today . ' 23:59:59'])
            ->get();
        $weeks = [];
        foreach ($orderGroups as $og) {
            $cb = Carbon::parse($og->created_at);
            if (isset($weeks[$cb->year . '-' . $cb->week])) {
                $weeks[$cb->year . '-' . $cb->week][0] += (int)Redis::get('ordergroups:' . $og->id . ':total_in_wave');
                $weeks[$cb->year . '-' . $cb->week][1] += (int)Redis::get('ordergroups:' . $og->id . ':sorted_pieces');
                $weeks[$cb->year . '-' . $cb->week][2] += (int)Redis::get('ordergroups:' . $og->id . ':total_pieces');
            } else {
                $weeks[$cb->year . '-' . $cb->week][0] = (int)Redis::get('ordergroups:' . $og->id . ':total_in_wave');
                $weeks[$cb->year . '-' . $cb->week][1] = (int)Redis::get('ordergroups:' . $og->id . ':sorted_pieces');
                $weeks[$cb->year . '-' . $cb->week][2] = (int)Redis::get('ordergroups:' . $og->id . ':total_pieces');
            }
        }
        $labels = [];
        foreach (array_keys($weeks) as $wk) {
            $dt = explode('-', $wk);
            $labels[] = Carbon::now()->setISODate($dt[0], $dt[1] - 1)->format('M-d');
        }
        $group = OrderGroup::whereBetween('created_at', [$startWeek, $today . ' 23:59:59'])->get();
        $pieChart = [0, 0, 0, 0, 0];

        foreach ($group as $g) {
            $divisions = json_decode(Redis::get('ordergroups:' . $g->id . ':divisions'), true);
            foreach ($divisions as $key => $dv) {
                $pieChart[$key] += $dv['pieces'];
            }
        }

        $ordersData = [
            'chartData' => $weeks,
            'labels' => $labels,
            'pieChart' => $pieChart
        ];

        return $ordersData;
    }
}
