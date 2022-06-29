<?php

namespace App\Repositories;

use App\Classes\Mail\MailSendGrill;
use App\Division;
use App\Http\Controllers\ApiResponses;
use App\Line;
use App\Managers\Admin\AdminSAALMAManager;
use App\Order;
use App\PalletContent;
use App\Pallets;
use App\Repositories\LineRepository;
use App\Repositories\OrderGroupRepository;
use App\Variation;
use App\Wave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Hash;
use Auth;

class WaveRepository extends BaseRepository
{
    protected $model = 'App\Wave';
    protected $cMail;

    public function __construct()
    {
        $this->lineRepository = new LineRepository();
        $this->orderGroupRepository = new OrderGroupRepository();
        $this->cMail = new MailSendGrill();
    }

    /**
     * Crea una Ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function createWave(array $waveData)
    {
        $lines = $waveData['lines'];
        unset($waveData['lines']);
        $wave = $this->create($waveData);
        $update = Line::whereIn('id', $lines)->update(['wave_id' => $wave->id]);
        return ApiResponses::createdWithObject($update);
    }

    /**
     * Crea una Ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function precalculateWave(array $waveData)
    {
        $businessRules = $waveData['business_rules'];
        $eagerLines = $this->lineRepository->findByWaveRules($waveData['order_group_id'], $businessRules, $waveData['inputSwitch']);
        $totalSku = $eagerLines->distinct()->count('sku');
        $totalPieces = $eagerLines->get()->sum('pieces');
        $totalOrders = $eagerLines->distinct()->count('order_id');
        $response = ['skus' => $totalSku, 'pieces' => $totalPieces, 'orders' => $totalOrders];
        return ApiResponses::okObject($response);
    }

    /**
     * Crea una Ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function createWaveNew(array $waveData)
    {
        $businessRules = $waveData['business_rules'];
        $eagerLines = $this->lineRepository->findByWaveRules($waveData['order_group_id'], $businessRules, $waveData['inputSwitch']);
        $lines = $eagerLines->get();
        $totalSku = $eagerLines->distinct()->count('sku');

        $totalPieces = $lines->sum('pieces');
        if ($totalPieces > 0) {
            $waveData['business_rules'] = json_encode($waveData['business_rules']);
            $division = Division::find($businessRules['divisions'][0]);
            $totalOrders = $eagerLines->distinct()->count('order_id');
            $maxSlots = 210;
            if ($totalOrders > $maxSlots) {
                return ApiResponses::ok('La ola resultante sería de ' . $totalOrders . ' salidas que supera las ' . $maxSlots . ' máximas posibles, por favor disminuya la cantidad de tiendas a surtir.');
            }
            $waveData['area_id'] = $division->processed_in;
            $statement = DB::select("SHOW TABLE STATUS LIKE 'waves'");
            $nextId = $statement[0]->Auto_increment;
            $waveData['wave_ref'] = $nextId;
            $wave = $this->create($waveData);
            $wave->pieces = $totalPieces;
            $wave->planned_pieces = $totalPieces;
            $wave->total_sku = $totalSku;
            $wave->available_skus = $totalSku;
            $wave->description = ($waveData['description']) ? strtoupper($waveData['description']) : null;
            $wave->priority_id = $waveData['priority'];
            $wave->save();

            $redisOgPieces =  Redis::get('ordergroups:' . $waveData['order_group_id'] . ':total_pending');
            $redisOgTotalInWave =  Redis::get('ordergroups:' . $waveData['order_group_id'] . ':total_in_wave');
            Redis::set('ordergroups:' . $waveData['order_group_id'] . ':total_pending', $redisOgPieces - $totalPieces);
            Redis::set('ordergroups:' . $waveData['order_group_id'] . ':total_in_wave', $redisOgTotalInWave + $totalPieces);
            $redisDivisionPieces = Redis::get('ordergroups:' . $waveData['order_group_id'] . ':divisions');
            $redisDivisionPieces = json_decode($redisDivisionPieces, true);
            foreach ($businessRules['divisions'] as $key => $division) {
                $divisionPieces = $lines->where('division_id', $division)->sum('pieces');
                switch ($division) {
                    case 3:
                        $redisDivisionPieces[2]['pending'] = (int)$redisDivisionPieces[2]['pending'] - (int)$divisionPieces;
                        break;
                    case 4:
                        $redisDivisionPieces[2]['pending'] = (int)$redisDivisionPieces[2]['pending'] - (int)$divisionPieces;
                        break;
                    case 5:
                        $redisDivisionPieces[3]['pending'] = (int)$redisDivisionPieces[3]['pending'] - (int)$divisionPieces;
                        break;
                    case 6:
                        $redisDivisionPieces[4]['pending'] = (int)$redisDivisionPieces[4]['pending'] - (int)$divisionPieces;
                        break;
                    case 7:
                        $redisDivisionPieces[4]['pending'] = (int)$redisDivisionPieces[4]['pending'] - (int)$divisionPieces;
                        break;
                    case 8:
                        $redisDivisionPieces[4]['pending'] = (int)$redisDivisionPieces[4]['pending'] - (int)$divisionPieces;
                        break;
                    default:
                        $redisDivisionPieces[$division - 1]['pending'] = (int)$redisDivisionPieces[$division - 1]['pending'] - (int)$divisionPieces;
                        $redisDivisionPieces[$division - 1]['in_wave'] = (int)$redisDivisionPieces[$division - 1]['in_wave'] + (int)$divisionPieces;
                        break;
                }
            }
            foreach ($eagerLines->cursor() as $els) {
                $els->update(['wave_id' => $wave->id]);
            }
            Redis::set('ordergroups:' . $waveData['order_group_id'] . ':divisions', json_encode($redisDivisionPieces));
        } else {
            return ApiResponses::badRequest('Las opciones seleccionadas no retornan ningún resultado.');
        }
        return ApiResponses::createdWithObject($wave);
    }

    /**
     * Obtiene una ola y sus contenidos.
     *
     * @param  Integer  $waveId
     * @return \Illuminate\Http\Response
     */
    public function getWave($waveId)
    {
        $wave = Wave::where('id', $waveId)->with('lines')->first();
        return ApiResponses::okObject($wave);
    }

    /**
     * Obtiene una ola y sus contenidos en formato Json adecuado para WAMAS.
     *
     * @param  Integer  $waveId
     * @return \Illuminate\Http\Response
     */
    public function getJson($waveId, $area, $maxslots = 120)
    {
        $limit = 210;
        $wave = Wave::find($waveId);
        if (!empty($wave)) {
            $orders = Order::whereHas('lines', function ($q) use ($waveId) {
                $q->where('wave_id', $waveId);
                $q->where('expected_pieces', '>', 0);
            })->with(['lines' => function ($q) use ($waveId) {
                $q->getBaseQuery()->orders = null;
                $q->where('wave_id', $waveId);
                $q->where('expected_pieces', '>', 0);
                $q->where('pieces_in_carton', '=', 0);
                $q->select(
                    'lines.id as lineNumber',
                    'order_id',
                    'lines.sku',
                    'divisions.name as division',
                    DB::raw('substr(product_classifications.jdaName, 8) classification'),
                    DB::raw('substr(product_families.jdaName, 5) category'),
                    'wave_priorities.name as priority',
                    'expected_pieces as pieces',
                    'lines.prepacks'
                );
                $q->join('divisions', 'lines.division_id', '=', 'divisions.id');
                $q->join('styles', 'lines.style_id', '=', 'styles.id');
                $q->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id');
                $q->join('product_families', 'styles.family_id', '=', 'product_families.id');
                $q->join('variations', 'lines.variation_id', '=', 'variations.id');
                $q->join('waves', 'lines.wave_id', '=', 'waves.id');
                $q->join('wave_priorities', 'waves.priority_id', '=', 'wave_priorities.id');
            }])
                ->where('store_id', '>', 0)
                ->select('id', 'id as orderNumber', 'routeNumber as route', 'storeDescription', 'routePriority', 'storeNumber as store', 'routeDescription', 'storePosition as storePriority', 'slots')
                ->orderBy('storePosition')->limit($limit)->get();

            $realSlots = count($orders);
            if ($wave->order_slots !== null) {
                $aSlots = json_decode($wave->order_slots);
                foreach ($orders as $key => $order) {
                    foreach ($aSlots as $aSlot) {
                        if ($order->id === $aSlot->order) {
                            $orders[$key]->slots = $aSlot->slots;
                        }
                    }
                }
            } else {
                $maxslots = ($maxslots < count($orders)) ? count($orders) : $maxslots;
                $maxslots = ($maxslots > $limit) ? $limit : $maxslots;
                $businessRules = json_decode($wave->business_rules, true);
                $divisions = $businessRules['divisions'];
                $sumslots = 0;
                if (!in_array(6, $divisions)) {
                    $maxppk = 0;
                    foreach ($orders as $key => $ord) {
                        $sumppks = 0;
                        foreach ($ord->lines as $key2 => $ln) {
                            $sumppks += $ln->prepacks;
                            $maxppk += $ln->prepacks;
                        }
                        $orders[$key]->sumppk = $sumppks;
                    }
                    foreach ($orders as $key3 => $ord) {
                        $percent = $ord->sumppk / $maxppk;
                        $slots = ($maxslots ?? 190) * $percent;
                        if (floor($slots) < 1) {
                            $slots = 1;
                        } elseif ($slots > 6) {
                            $slots = 6;
                        }
                        $orders[$key3]->slots = floor($slots);
                        $slotsByStore[] = [
                            'slots' => $orders[$key3]->slots,
                            'store' => $orders[$key3]->store
                        ];
                        $sumslots += floor($slots);
                    }
                    $ordersCalculate = $this->calculateSlots($maxslots, $sumslots, $slotsByStore, $orders);
                    $orders = $ordersCalculate[0];
                    $realSlots = $ordersCalculate[1];
                }
            }


            $binsObj = Pallets::where('wave_id', $waveId)->select('lpn_transportador')->get();
            $bins = [];
            foreach ($binsObj as $key => $bin) {
                $bins[] = $bin->lpn_transportador;
            }

            $waveArray = ['wave' => ['waveNumber' => $wave->wave_ref, 'businessName' => 'Comercializadora Almacenes Garcia SA de CV', 'area' => $area, 'orders' => $orders, 'bins' => $bins, 'maxslots' => $realSlots, 'maxppk' => $maxppk ?? 0]];

            $waveArray = json_encode($waveArray, JSON_PRETTY_PRINT);
            $filename = '/to_ssi/' . sprintf("%08d", $waveId);
            Storage::disk('sftpwamas')->put($filename . '.tmp', $waveArray);
            Storage::disk('sftpwamas')->move($filename . '.tmp', $filename . '.json');
            return ApiResponses::ok();
        } else {
            return ApiResponses::notFound('Ola ' . $waveId . ' no existe');
        }
    }

    /**
     * @param $maxSlots
     * @param $sumSlots
     * @param $slotsByStore
     * @param $orders
     * @return mixed
     */
    public function calculateSlots($maxSlots, $sumSlots, $slotsByStore, $orders, $save = false)
    {
        usort($slotsByStore, function ($a, $b) {
            if ($a['slots'] == $b['slots']) {
                return 0;
            }
            return $a['slots'] < $b['slots'] ? 1 : -1;
        });
        $slotsOrder = [];
        $sum = $sumSlots;
        if ($maxSlots < $sum) {
            while ($maxSlots < $sum) {
                foreach ($slotsByStore as $key => $slot) {
                    if ($maxSlots <= $sum) {
                        if ($slot['slots'] > 1) {
                            $slotsByStore[$key] = [
                                'slots' => $slot['slots'] - 1,
                                'store' => $slot['store']
                            ];
                            $sum = $sum - 1;
                        }
                    } else {
                        break;
                    }
                }
            }
            foreach ($orders as $key => $ord) {
                foreach ($slotsByStore as $slot) {
                    if ($orders[$key]->store === $slot['store']) {
                        if ($save === true) {
                            $slotsOrder[] = ['order' => $ord->id, 'slots' => (int)$slot['slots']];
                        }
                        $orders[$key]->slots = $slot['slots'];
                    }
                }
            }
        }
        return [$orders, $sum, $slotsOrder];
    }


    /**
     * Obtiene json de devolucion de ola.
     *
     * @param  Integer  $waveId
     * @return \Illuminate\Http\Response
     */
    public function getJsonDevolution($waveId, $area)
    {
        $wave = Wave::find($waveId);
        if (!empty($wave)) {
            $lines = Line::where('wave_id', $waveId)
                ->where('expected_pieces', '>', 0)
                ->select(
                    'lines.id as lineNumber',
                    'order_id',
                    'lines.sku',
                    'divisions.name as division',
                    DB::raw('substr(product_classifications.jdaName, 8) classification'),
                    DB::raw('substr(product_families.jdaName, 5) category'),
                    'priorities.jda_name as priority',
                    DB::raw('sum(expected_pieces) pieces'),
                    DB::raw('sum(prepacks) prepacks')
                )
                ->join('divisions', 'lines.division_id', '=', 'divisions.id')
                ->join('styles', 'lines.style_id', '=', 'styles.id')
                ->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id')
                ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                ->join('variations', 'lines.variation_id', '=', 'variations.id')
                ->join('priorities', 'variations.priority_id', '=', 'priorities.id')
                ->groupBy('lines.sku')
                ->get();
            $orders = [];
            foreach ($lines as $key => $ln) {
                $orders[] = [
                    "id" => $ln->sku,
                    "orderNumber" => $ln->sku,
                    "route" => substr($ln->order->ordergroup->local, 2),
                    "storeDescription" => "CEDIS",
                    "routePriority" => 1,
                    "store" => $ln->sku,
                    "routeDescription" => "DEVOLUCION",
                    "storePriority" => $key,
                    "slots" => 1,
                    "lines" => [[
                        "lineNumber" => $ln->sku,
                        "order_id" => $ln->sku,
                        "sku" => $ln->sku,
                        "division" => $ln->division,
                        "classification" => $ln->classification,
                        "category" => $ln->category,
                        "priority" => $ln->priority,
                        "pieces" => $ln->pieces,
                        "prepacks" => $ln->prepacks,
                    ]]
                ];
            }

            $binsObj = Pallets::where('wave_id', $waveId)->select('lpn_transportador')->get();
            $bins = [];
            foreach ($binsObj as $key => $bin) {
                $bins[] = $bin->lpn_transportador;
            }

            $waveArray = ['wave' => ['waveNumber' => $wave->id . '-D', 'businessName' => 'Comercializadora Almacenes Garcia SA de CV', 'area' => $area, 'orders' => $orders, 'bins' => $bins]];

            $waveArray = json_encode($waveArray, JSON_PRETTY_PRINT);
            $filename = '/to_ssi/' . sprintf("%08d", $waveId);
            Storage::disk('sftpwamas')->put($filename . '.tmp', $waveArray);
            Storage::disk('sftpwamas')->move($filename . '.tmp', $filename . '.json');
            return ApiResponses::ok();
        } else {
            return ApiResponses::notFound('Ola ' . $waveId . ' no existe');
        }
    }

    /**
     * Obtiene json de devolucion de ola por un excel.
     *
     * @param  Integer  $waveId
     * @return \Illuminate\Http\Response
     */
    public function getJsonXls($file, $area, $waveName)
    {
        $ext = $file->extension();
        $wave = [];
        $orders = [];
        if ($ext == 'xlsx' || $ext == 'xls') {
            $path = $file->store('excel', 'local');

            $rows = SimpleExcelReader::create(storage_path('app') . '/' . $path)
                ->getRows();

            foreach ($rows as $key => $row) {
                if (array_key_exists("SKU", $row)) {
                    array_key_exists($row["SKU"], $wave) ? $wave[$row["SKU"]] += $row["PIEZAS"] : $wave[$row["SKU"]] = $row["PIEZAS"];
                }
            }
            $variations = Variation::whereIn('sku', array_keys($wave))
                ->select(
                    'variations.sku',
                    'divisions.name as division',
                    DB::raw('substr(product_classifications.jdaName, 8) classification'),
                    DB::raw('substr(product_families.jdaName, 5) category'),
                    'priorities.label as priority'
                )
                ->join('divisions', 'variations.division_id', '=', 'divisions.id')
                ->join('styles', 'variations.style_id', '=', 'styles.id')
                ->join('product_classifications', 'styles.classification_id', '=', 'product_classifications.id')
                ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                ->join('priorities', 'variations.priority_id', '=', 'priorities.id')
                ->get();

            foreach ($variations as $key => $var) {
                $orders[] = [
                    "id" => $var->sku,
                    "orderNumber" => $var->sku,
                    "route" => '110',
                    "storeDescription" => "CEDIS",
                    "routePriority" => 1,
                    "store" => $var->sku,
                    "routeDescription" => "DEVOLUCION",
                    "storePriority" => $key,
                    "slots" => 1,
                    "lines" => [[
                        "lineNumber" => $var->sku,
                        "order_id" => $var->sku,
                        "sku" => $var->sku,
                        "division" => $var->division,
                        "classification" => $var->classification,
                        "category" => $var->category,
                        "priority" => $var->priority,
                        "pieces" => $wave[$var->sku],
                        "prepacks" => $wave[$var->sku],
                    ]]
                ];
            }
            $waveArray = ['wave' => ['waveNumber' => $waveName, 'businessName' => 'Comercializadora Almacenes Garcia SA de CV', 'area' => $area, 'orders' => $orders, 'bins' => ['B00003427291']]];

            $waveArray = json_encode($waveArray, JSON_PRETTY_PRINT);
            $filename = '/to_ssi/' . sprintf("%08d", $waveName);
            Storage::disk('sftpwamas')->put($filename . '.tmp', $waveArray);
            Storage::disk('sftpwamas')->move($filename . '.tmp', $filename . '.json');

            return ApiResponses::okObject($orders);
        }
        return ApiResponses::okObject(['mensaje' => 'nani?']);
    }

    /**
     * Obtiene json para registrar ola en SAALMA.
     *
     * @param  $waveId
     * @return Array
     */
    public function getRegisterRequest($waveId)
    {
        $wave = Wave::find($waveId);
        $waveLines = $this->lineRepository->waveLinesExpectedSumBySku($wave);
        $registerRequest = [];
        $now = new \DateTime();
        if ($wave->ordergroup) {
            $local = $wave->ordergroup->local;
            $allocation = $wave->ordergroup->allocation;
            $allocationGroup = $wave->ordergroup->allocationgroup;
            $transferencia = $wave->ordergroup->transferencia;
            $almacen = "20";
            $destination = "21";
            $isBIS = str_ends_with($wave->ordergroup->reference, '.BIS');
        } else {
            $allocation = "";
            $transferencia = "";
            $local = "10102";
            $almacen = "02";
        }

        $businessRules = json_decode($wave->business_rules, true);
        $divisions = $businessRules['divisions'];
        $registerRequest['codigoOla'] = $wave->id;
        $registerRequest['allocation'] = $allocation;
        $registerRequest['allocationGroupId'] = $allocationGroup ?? null;
        $registerRequest['transferencia'] = $transferencia;
        $registerRequest['fechaOla'] = $now->format('Y-m-d\TH:i:s');
        $registerRequest['noMovs'] = $waveLines->count();
        $registerRequest['localOrigen'] = $local;
        $registerRequest['almacenOrig'] = $almacen;
        $registerRequest['detalleOla'] = [];
        $mov = $waveId * 10000;
        foreach ($waveLines as $key => $wl) {
            $folio = $mov + $key;
            $detalle = [];
            $detalle['folioMov'] = $folio;
            $detalle['almacenDest'] = $destination;
            $detalle['sku'] = (string)$wl->sku;
            $detalle['familia'] = (string)$wl->familia;
            $detalle['familiaDescripcion'] = (string)$wl->familiaDescripcion;
            $detalle['cantidad'] = $wl->cantidad;
            $detalle['prioridad'] = $wl->familiaRanking ?? ($isBIS === true ? $wl->style_id : $wl->department->ranking);
            $registerRequest['detalleOla'][] = $detalle;
        }
        return $registerRequest;
    }

    /**
     * Obtiene todas las olas.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll(Request $request)
    {
        $wave = Wave::where(
            function ($q) use ($request) {
                $dateInit = ($request->dateInit) ? Carbon::parse($request->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
                $dateEnd = ($request->dateEnd) ? Carbon::parse($request->dateEnd)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
                $filter = ($request->search) ? $request->search : null;
                if ($request->has('status') && $request->status >= 0 && $dateInit !== null && $filter == null) {
                    return $q
                        ->orWhere('status', $request->status)
                        ->wherebetween('created_at', [$dateInit, $dateEnd]);
                } elseif (($request->status < 0 || !$request->has('status'))  && $dateInit !== null && $filter == null) {
                    return $q
                        ->where('status', '!=', 0)
                        ->wherebetween('created_at', [$dateInit, $dateEnd]);
                } elseif ($filter !== null && $dateInit !== null) {
                    if ($request->status < 0) {
                        return $q
                            ->where('status', '!=', 0)
                            ->wherebetween('created_at', [$dateInit, $dateEnd])
                            ->whereRaw(DB::raw('(id LIKE ' . '"%' . $filter . '%" OR description LIKE ' . '"%' . $filter . '%")'));
                    } else {
                        return $q
                            ->where('status', $request->status)
                            ->wherebetween('created_at', [$dateInit, $dateEnd])
                            ->whereRaw(DB::raw('(id LIKE ' . '"%' . $filter . '%" OR description LIKE ' . '"%' . $filter . '%")'));
                    }
                } elseif ($dateInit === null && $request->status < 0) {
                    return $q->where('status', '!=', 0);
                } elseif ($dateInit === null && $request->status >= 0) {
                    return $q->where('status', $request->status);
                }
            }
        )
            ->select(
                'id',
                'created_at',
                'updated_at',
                'area',
                'wave_ref',
                'area_id',
                'order_group_id',
                'business_rules',
                'pieces',
                'complete',
                'status',
                'sorted_pieces',
                'picked_pieces',
                'picking_end',
                'picking_start',
                'planned_pieces',
                'verified_stock',
                'description',
                'total_sku',
                'induction_start',
                'induction_end',
                'verify_slots',
                'available_skus',
                'priority_id',
                'picked_boxes',
                'sorted_boxes',
                'sorted_prepacks',
                'prepacks'
            )
            ->with('ordergroup')
            ->with(['pallets' => function ($q) {
                $q->select(
                    'wave_id',
                    DB::raw('SUM(IF(status in (1, 2, 3, 4), 1, 0)) as received_bins'),
                    DB::raw('count(*) as total_bins')
                )->groupBy('wave_id');
            }])
            ->orderBy('id', 'desc')
            ->get();

        if ($request->paginated === 'true') {
            $result = collect($wave);
            if (isset($request->orderBy)) {
                if ($request->orderDirection == 'asc') {
                    $result = $result->sortBy($request->orderBy);
                } else {
                    $result = $result->sortByDesc($request->orderBy);
                }
            }
            $result = $result->paginate((int) $request->size);
        }
        return ApiResponses::okObject($result);
    }

    /**
     * @param $wave
     * @return int
     */
    public function getBoxesWave($wave)
    {
        $boxes = 0;
        $totalBox = PalletContent::where('wave_id', $wave)->select('cajas')->get()->toArray();
        foreach ($totalBox as $key => $value) {
            $boxes += $value['cajas'];
        }
        return $boxes;
    }

    public function getCurrentPicking()
    {
        $waves = Wave::whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING, Wave::COMPLETED])
            ->select('id', 'planned_pieces', 'stock_pieces', 'picked_pieces', 'picked_boxes', 'total_sku', 'available_skus')
            ->with(['pickedSkus' => function ($q) {
                $q->select('wave_id', DB::raw('count(distinct(sku)) as skus'))->groupBy('wave_id');
            }])
            ->with(['pallets' => function ($q) {
                $q->select(
                    'wave_id',
                    DB::raw('SUM(IF(status in (1, 2, 3, 4), 1, 0)) as received_bins'),
                    DB::raw('count(*) as total_bins')
                )->groupBy('wave_id');
            }])
            ->limit(100)->orderByDesc('id')->get();
        return ApiResponses::okObject($waves);
    }

    public function getProgressDepartments($waveId)
    {
        $wave = Wave::where('id', $waveId)
            ->with('linesProgress')
            ->with('linesDetail')
            ->limit(100)->orderByDesc('id')->get();
        return ApiResponses::okObject($wave);
    }

    /**
     * Obtiene todas las olas.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllForPicking($request)
    {
        if ($request->paginated === 'true') {
            $object = Wave::whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
                ->with('linesDetail')
                ->with('linesProgress')
                ->orderByDesc('id')
                ->get();
            $result = collect($object);
            if (isset($request->orderBy)) {
                if ($request->orderDirection == 'asc') {
                    $result = $result->sortBy($request->orderBy);
                } else {
                    $result = $result->sortByDesc($request->orderBy);
                }
            }
            $result = $result->paginate((int) $request->size);
            return $result;
        } else {
            $select = [
                'waves.id',
                'waves.wave_ref',
                'waves.pieces',
                'waves.status',
                'departments.name',
                'variations.department_id',
                DB::raw('divisions.name AS dept'),
                DB::raw('SUM(pallet_contents.cantidad) AS pzas'),
                DB::raw('SUM(pallet_contents.cajas) AS cajas'),
                DB::raw('count(*) AS skus')
            ];

            $wave = Wave::whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
                ->select($select)
                ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
                ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
                ->join('variations', 'variations.id', '=', 'pallet_contents.variation_id')
                ->join('departments', 'variations.department_id', '=', 'departments.id')
                ->join('divisions', 'departments.division_id', '=', 'divisions.id')
                ->groupBy(
                    'waves.id',
                    'waves.wave_ref',
                    'waves.status',
                    'waves.pieces',
                    'variations.department_id',
                    'departments.name',
                    'divisions.name'
                )
                ->orderByDesc('id')
                ->get()
                ->toArray();

            $completed = Wave::whereIn('waves.status', [Wave::PICKING, Wave::SORTING])
                ->select(
                    'waves.id',
                    'lines.department_id',
                    DB::raw('SUM(lines.expected_pieces) AS expected_pieces'),
                    DB::raw('COUNT(*) AS rpt')
                )
                ->join('lines', 'waves.id', '=', 'lines.wave_id')
                ->where('lines.expected_pieces', '<', 0)
                ->groupBy(
                    'waves.id',
                    'lines.department_id'
                )->orderBy('department_id')
                ->get()
                ->toArray();
            $object = [];
            foreach ($wave as $waves) {
                foreach ($completed as $revision) {
                    if ($waves['id'] == $revision['id'] && $waves['department_id'] == $revision['department_id']) {
                        $object[] = array_merge($waves, $revision);
                    }
                }
            }
            $result = collect($object);
            if (isset($request->orderBy)) {
                if ($request->orderDirection == 'asc') {
                    $result = $result->sortBy($request->orderBy);
                } else {
                    $result = $result->sortByDesc($request->orderBy);
                }
            }
            $result = $result->paginate((int) $request->size);
            return $result;
        }

        return ApiResponses::okObject($object);
    }

    /**
     * @param $oRequest
     * @return \Illuminate\Http\Response
     */
    public function getAllForFinished($oRequest)
    {
        $object = Wave::where(function ($q) use ($oRequest) {
            $waveId = ($oRequest->wave > 0) ? $oRequest->wave : null;
            $dateInit = ($oRequest->dateInit) ? Carbon::parse($oRequest->dateInit)->format('Y-m-d') . ' 00:00:00' : null;
            $dateEnd = ($oRequest->dateEnd) ? Carbon::parse($oRequest->dateEnd)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
            if ($waveId !== null) {
                return $q->where('waves.id', $waveId);
            }
            if ($dateInit !== null) {
                return $q
                    ->wherebetween('waves.created_at', [$dateInit, $dateEnd]);
            }
        })
            ->where('waves.status', '=', Wave::COMPLETED)
            ->with('linesDetail')
            ->orderByDesc('id')
            ->get();

        $result = collect($object);
        if (isset($oRequest->orderBy)) {
            if ($oRequest->orderDirection == 'asc') {
                $result = $result->sortBy($oRequest->orderBy);
            } else {
                $result = $result->sortByDesc($oRequest->orderBy);
            }
        }
        $result = $result->paginate((int) $oRequest->size);

        return ApiResponses::okObject($result);
    }

    /**
     * @param $wave_id
     * @param null $department_id
     * @param int $paginate
     * @return \Illuminate\Http\Response
     */
    public function getDetailsWaveRef($wave_id, $department_id = null, $paginate = 20)
    {
        if ($department_id) {
            $pallets = PalletContent::select('pallet_id')
                ->where([['wave_id', '=', $wave_id], ['department_id', '=', $department_id]])
                ->groupBy('pallet_id')
                ->pluck('pallet_id');

            $result = Pallets::whereIn('id', $pallets)
                ->with(['palletsSku' => function ($query) use ($department_id) {
                    $query->where('department_id', '=', $department_id);
                }])
                ->paginate($paginate);
        } else {
            $result = Pallets::where('wave_id', '=', $wave_id)->with('palletsSku')->paginate($paginate);
        }
        return ApiResponses::okObject($result);
    }

    /**
     * Actualiza una ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function updateWave($model, array $waveData)
    {
        // $lines = $waveData['lines'];
        // unset($waveData['lines']);
        $wave = $this->update($model, $waveData);
        // $update1 = Line::where('wave_id', $model->id)->update(['wave_id' => null]);
        // $update2 = Line::whereIn('id', $lines)->update(['wave_id' => $wave->id]);
        return ApiResponses::okObject($wave);
    }

    /**
     * Elimina una Ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function deleteWave($model)
    {
        $update1 = Line::where('wave_id', $model->id)->update(['wave_id' => null]);
        return $this->delete($model->id);
    }

    /**
     * Cancela una Ola.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function cancelWave($wave, $oRequest)
    {
        if ($wave->status === $wave::CANCELLED) {
            //return $wave;
        }
        $userId = Auth::id();

        if ($oRequest['reason'] == 'Equivocación') {
            $reasonId = 1;
        } elseif ($oRequest['reason'] == 'Insuficiente personal') {
            $reasonId = 2;
        } elseif ($oRequest['reason'] == 'Operativamente inviable') {
            $reasonId = 3;
        } else {
            $reasonId = 4;
        }

        Line::where('wave_id', $wave->id)
            ->update(['wave_id' => null]);
        $wave->canceled_by_user_id = $userId;
        $wave->reason_cancel_wave_id = $reasonId;
        $wave->status = $wave::CANCELLED;
        $this->orderGroupRepository->calculatePiecesForRedis($wave->ordergroup);
        $wave->save();
        return $wave;
    }

    /**
     * Crea una ola de devolución.
     *
     * @param  Array  $waveData
     * @return \Illuminate\Http\Response
     */
    public function makeDevolutionWave($transferList)
    {
        $skus = [];
        $orders = [];
        $total = 0;
        $transfers = [];
        foreach ($transferList as $key => $trf) {
            $total += $trf['totalPiezas'];
            $skus[$trf['sku']] = isset($skus[$trf['sku']]) ? $skus[$trf['sku']] + $trf['totalPiezas'] : $trf['totalPiezas'];
            $transfers[$trf['transferencia']] = 1;
        }

        $statement = DB::select("SHOW TABLE STATUS LIKE 'waves'");
        $nextId = $statement[0]->Auto_increment;
        $wave = $this->create([
            'pieces' => $total,
            'complete' => 0,
            'status' => 2,
            'business_rules' => '{"divisions":[],"divisionsNames":["Devoluciones"],"excludedDepartments":[],"excludedDepartmentsNames":[],"excludedRoutes":[],"excludedRoutesNames":[],"excludedClassifications":[],"excludedClassificationsNames":[],"excludedFamilies":[],"excludedFamiliesNames":[]}',
            'order_group_id' => 0,
            'wave_ref' => $nextId,
            'sorted_pieces' => 0,
            'picked_pieces' => 0,
            'verified_stock' => 1,
            'description' => 'OLA DE DEVOLUCION'
        ]);


        foreach ($skus as $sku => $pzs) {
            $orders[$sku]['lines'][$sku] = [
                'sku'          => $sku,
                'style_id'     => (int)Redis::get('sku:' . $sku . ':style'),
                'pieces'       => $pzs,
                'ppk'          => 1,
                'department_id' => (int)Redis::get('sku:' . $sku . ':department'),
                'division_id'  => (int)Redis::get('sku:' . $sku . ':division'),
                'variation_id' => (int)Redis::get('sku:' . $sku . ':id'),
                'wave_id'      => $wave->id,
                'prepacks'     => $pzs,
                'expected_pieces'  => $pzs,
            ];
        }

        foreach ($orders as $key => $ord) {
            $order = new Order;
            $order->store_id = 0;
            $order->storePriority = 1;
            $order->routePriority = 1;
            $order->routeNumber = 102;
            $order->storeNumber = $key;
            $order->routeDescription = 'DEVOLUCION';
            $order->storeDescription = 'CEDIS';
            $order->status = 1;
            $order->order_group_id = 0;
            $order->slots = 1;
            $order->save();
            $order->lines()->createMany($ord['lines']);
            unset($orders[$key]);
            unset($ord['lines']);
            unset($order);
        }
        $transfersToRequest = [];
        foreach ($transfers as $trft => $val) {
            $transfersToRequest[] = [
                'folioMov' => (int)(($wave->id * 100) . $trft),
                'transferencia' => $trft,
                'almacenDest' => '21'
            ];
        }


        $registerRequest = [
            'codigoOla' => (int)($wave->id * 100),
            'noMovs'    => count($transfers),
            'detalleOla' => $transfersToRequest,
        ];

        $saalmaManager = new AdminSAALMAManager();
        $registerWave = $saalmaManager->registerDevolutionWave($registerRequest);

        return $registerWave;
    }

    public function test()
    {
        $obj = Wave::with('linesSkuSeeder')->get()->toArray();
        return ApiResponses::okObject($obj);
    }

    public function getDestinations($waveId)
    {
        $wave = Wave::find($waveId);
        $bussinesRules = json_decode($wave->business_rules);
        $area = '';

        foreach ($bussinesRules->divisions as $division) {
            $area = ($division > 5) ? 'ptl' : 'sorter';
        }
        $og = new OrderGroupRepository;
        $parseOrders = $og->parseOrders($wave, $area, $wave->verify_slots);

        $wave->verify_slots = true;
        $wave->save();
        $wave = Wave::find($waveId);
        $aSlots = json_decode($wave->order_slots);
        foreach ($parseOrders[0] as $key => $parseOrder) {
            foreach ($aSlots as $aSlot) {
                if ($parseOrder->id === $aSlot->order) {
                    $parseOrders[0][$key]->slots = $aSlot->slots;
                }
            }
        }
        return $parseOrders;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getAllInSorter($request)
    {
        if ($request->wave_id > 0) {
            $waves = Wave::where('waves.id', $request->wave_id)
                ->get();
        } else {
            $waves = Wave::where('status', Wave::SORTING)
                ->where('area', $request->area)
                ->get();
            foreach ($waves as $key => $wave) {
                $total_bins = Pallets::where('wave_id', $wave->id)->count();
                $received_bins = Pallets::where('wave_id', $wave->id)
                    ->whereIn('status', [Pallets::STAGING, Pallets::MOVING, Pallets::BUFFER, Pallets::INDUCTION])
                    ->count();
                $waves[$key]->total_bins = $total_bins;
                $waves[$key]->received_bin = $received_bins;
            }
        }

        return $waves;
    }

    /**
     * @param $init
     * @param null $end
     * @return mixed
     */
    public function getAllWaveFinished($init, $end = null)
    {
        $dateInit = ($init) ? Carbon::parse($init)->format('Y-m-d') . ' 00:00:00' : Carbon::now()->format('Y-m-d') . ' 00:00:00';
        $dateEnd = ($end != null) ? Carbon::parse($end)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
        $waves = Wave::where('status', Wave::COMPLETED)->whereBetween('induction_end', [$dateInit, $dateEnd])->get();
        return $waves;
    }

    /**
     * @param $startWeek
     * @param $endWeek
     * @param $today
     * @return mixed
     */
    public function getWaveStats($startWeek, $endWeek, $today)
    {
        $wavesInProgress = Wave::select(
            'id',
            'area',
            'pieces',
            'sorted_pieces',
            'sorted_prepacks',
            'prepacks',
            DB::raw('ROUND(sorted_prepacks/(TIMESTAMPDIFF(MINUTE, induction_start, updated_at))) as ppk_minute')
        )
            ->where('status', Wave::SORTING)->get();
        $wavesToday = Wave::where('status', Wave::COMPLETED)
            ->where('induction_end', '>', $today)
            ->count();
        $wavesWeek = Wave::where('status', Wave::COMPLETED)
            ->whereBetween('induction_end', [$startWeek, $endWeek])
            ->count();
        $tenWeek = Carbon::parse($startWeek)->subWeeks(10)->format('Y-m-d') . ' 00:00:00';
        $wavesChart = Wave::select(
            DB::raw('YEARWEEK(induction_end) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', Wave::COMPLETED)
            ->whereBetween('induction_end', [$tenWeek, $today . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('count', 'date')
            ->toArray();
        $period = new \Carbon\CarbonPeriod($tenWeek, '1 week', $today);
        foreach ($period as $date) {
            if (!isset($waves[$date->year() . $date->week()])) {
                $waves[$date->year() . $date->week()] = 0;
            }
        }
        ksort($wavesChart);
        $waves = [
            'wavesToday' => $wavesToday,
            'wavesWeek' => $wavesWeek,
            'wavesChart' => $wavesChart,
            'wavesInProg' => $wavesInProgress
        ];
        return $waves;
    }

    /**
     * @param $wave
     * @return mixed
     */
    public function getDepartmentsByWave($wave)
    {
        $departments = Wave::where('waves.id', $wave)
            ->join('lines', 'lines.wave_id', '=', 'waves.id')
            ->join('departments', 'departments.id', '=', 'lines.department_id')
            ->select('departments.*')
            ->distinct()
            ->orderBy('ranking', 'ASC')
            ->get();
        return $departments;
    }

    /**
     * @param $area
     * @param $init
     * @param $end
     * @return mixed
     */
    public function getIdsWaves($area, $init, $end)
    {
        $dateInit = ($init != "") ? Carbon::parse($init)->format('Y-m-d') . ' 00:00:00' : Wave::where('status', Wave::COMPLETED)->select('created_at')->orderBy('created_at', 'asc')->first();
        $dateEnd = ($end != "") ? Carbon::parse($end)->format('Y-m-d') . ' 23:59:59' : Carbon::now()->format('Y-m-d') . ' 23:59:59';
        $waves = Wave::where('status', Wave::COMPLETED)
            ->whereBetween('created_at', [$dateInit, $dateEnd])
            ->where(
                function ($q) use ($area) {
                    return $q
                        ->whereHas('cartons', function ($q) use ($area) {
                            if ($area != '') {
                                $q->where('area', $area);
                            }
                        });
                }
            )
            ->get();
        return $waves;
    }

    /**
     * @param $oRequest
     * @return mixed
     */
    public function getExcelWithSkusIntoInventory($array)
    {
        $skusFromSaalma = [];
        $aParseArray = [];
        $arraySkus = [];
        $arrayParse = [];
        $saalmaManager = new AdminSAALMAManager();


        foreach ($array as $key => $item) {
            $array[$key] =  array_change_key_case($item, CASE_LOWER);
        }

        foreach ($array as $sku) {
            if (count($sku) >= 1) {
                $aParseArray[] = [
                    'sku' => $sku['sku'] ?? 100000,
                ];
            }
        }
        foreach ($aParseArray as $skus) {
            $arraySkus[] = $skus['sku'];
        }

        $arraySkuList = [
            "almacen" => 20,
            "skuList" => $arraySkus
        ];

        $resultResponse = $saalmaManager->getInventoryDev($arraySkuList);

        foreach ($resultResponse as $data) {
            $skusFromSaalma[] = $data['sku'];
        }

        $diffArray = array_diff($arraySkus, $skusFromSaalma);

        foreach ($diffArray as $key => $item) {
            $arrayParse[] = [
                'sku' => (string)$item,
                'almacenId' => '20',
                'catidadPzas' => 0,
                'prepack' => 0
            ];
        }

        $totalSkus = array_merge($resultResponse, $arrayParse);

        return $totalSkus;
    }

    /**
     * @param $oRequest
     * @return $dataResponse
     */
    public function checkPassword($oRequest)
    {
        $password = $oRequest['password'];
        $user = Auth::user();
        if (Hash::check($password, $user->password)) {
            $dataResponse = [
                'status'    => false,
                'message'   => 'Contraseña correcta'
            ];
        } else {
            $dataResponse = [
                'status'    => true,
                'message'   => 'Contraseña incorrecta'
            ];
        }

        return $dataResponse;
    }

    /**
     * @param $oRequest
     * @return $dataResponse
     */
    public function cancelWaveInPicking($oRequest)
    {
        $userId = (int)$oRequest['userId'];
        $waveId = (int)$oRequest['waveId'];

        if ($oRequest['reason'] == 'Equivocación') {
            $reasonId = 1;
        } elseif ($oRequest['reason'] == 'Insuficiente personal') {
            $reasonId = 2;
        } elseif ($oRequest['reason'] == 'Operativamente inviable') {
            $reasonId = 3;
        } else {
            $reasonId = 4;
        }

        $wave = Wave::find($waveId);

        $wave->lines()->update(['wave_id' => null]);
        $wave->canceled_by_user_id = $userId;
        $wave->reason_cancel_wave_id = $reasonId;
        $wave->status = Wave::CANCELLED;
        $wave->save();

        $this->orderGroupRepository->calculatePiecesForRedis($wave->ordergroup);

        $dataResponse = [
            'status'    => 200,
            'message'   => 'Ola cancelada con exito'
        ];

        return $dataResponse;
    }

    /**
     * @param $oRequest
     * @return $dataResponse
     */
    public function cancelWaveNew($oRequest)
    {
        $waveId = (int)$oRequest['waveId'];
        $wave = Wave::find($waveId);

        $password = $oRequest['password'];
        $reasonId = 1;
        $user = Auth::user();
        if ($wave->status != 1) {
            if (!Hash::check($password, $user->password)) {
                return $dataResponse = [
                    'status'    => false,
                    'message'   => 'Contraseña incorrecta'
                ];
            }
        }
        $wave->lines()->update(['wave_id' => null]);
        $wave->canceled_by_user_id = $user->id;
        $wave->reason_cancel_wave_id = $reasonId;
        $wave->status = Wave::CANCELLED;
        $wave->save();

        $this->orderGroupRepository->calculatePiecesForRedis($wave->ordergroup);

        $dataResponse = [
            'status'    => true,
            'message'   => 'Ola cancelada con exito'
        ];

        return $dataResponse;
    }

    /**
     * @param $oRequest
     * @return bool
     */
    public function finishWave($oRequest)
    {
        $adminSaalma = new AdminSAALMAManager;
        $registerRequest = [];
        $wave = Wave::find($oRequest->wave);
        if ($wave) {
            $wave->status = Wave::COMPLETED;
            $wave->induction_end = new \DateTime();
            $wave->save();
            $registerRequest["olaID"] = $wave->id;
            $adminSaalma->waveFinished($registerRequest);
            return true;
        }
        return false;
    }
    /**
     * @param $oRequest
     * @return ApiResponses
     */

    public function updateWaveSlots($request)
    {
        $wave = Wave::find($request->wave);
        if (!empty($wave)) {
            $slots = array_values($request->slots);
            $order_slots = [];
            if (array_sum($slots) > 210) {
                return ApiResponses::badRequest('Número de salidas exceden las posibles en sorter');
            } else {
                foreach ($request->slots as $k => $s) {
                    $order_slots[] = ['order' => $k, 'slots' => $s];
                }
                $wave->order_slots = json_encode($order_slots);
                $wave->save();
                return ApiResponses::okObject($wave);
            }
        }
    }

    /**
     * @return bool
     */
    public function wavePriorities()
    {
        $wavePriorities = WavePriority::all();
        return $wavePriorities;
    }
}
