<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Wave;
use App\Line;
use App\PalletContent;
use App\Pallets;
use App\PalletMovement;
use App\Repositories\PalletRepository;
use App\Zone;
use Exception;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;

class PalletsController extends Controller
{
    protected $palletRepository;

    public function __construct()
    {
        $this->palletRepository = new PalletRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        try {
            $pallets= $this->palletRepository->getAllPallets();
            return ApiResponses::okObject($pallets);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    public function generateOrder(Request $request)
    {
        try {
            $order= $this->palletRepository->generateOrder($request);
            ;
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getPalletsOrder(Request $request)
    {
        try {
            $order= $this->palletRepository->getPalletsOrder($request);
            ;
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getPalletsBuffer(Request $request)
    {
        try {
            $order= $this->palletRepository->getPalletsBuffer($request);
            ;
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'codigoOla' => 'required|integer',
            'fechaMov' => 'required|string',
            'almacenDest' => 'required|string',
            'ubicacionDest' => 'required|string',
            'detalleTransportador' => 'required|array',
            'detalleTransportador.*.folioMov' => 'required|integer',
            'detalleTransportador.*.sku' => 'required|integer',
            'detalleTransportador.*.cantidad' => 'required|integer',
            'detalleTransportador.*.cajas' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }
        try {
            $wave = Wave::find($request->codigoOla);
            $pickedBoxes = $wave->picked_boxes;
            $pickedPieces = $wave->picked_pieces;
            $lpn = Pallets::where(
                [
                    ['wave_id','=',$request->codigoOla],
                    ['lpn_transportador','=',$request->lpnTransportador]
                ]
            )->first();
            if ($lpn!=null) {
                return response(["exito"=> true,"message" => "El LPN ya ha sido recibido para esta OLA"], 200);
            }
        } catch (Exception $e) {
            Log::error('Error en '.__METHOD__.' lÃ­nea '.$e->getLine().':'.$e->getMessage());
            return ApiResponses::internalServerError($e);
        }

        $zone = Zone::where('code', '=', $request->ubicacionDest)->first();

        $id = Pallets::create([
            'wave_id'           => $request->codigoOla,
            'fecha_mov'         => $request->fechaMov,
            'lpn_transportador' => $request->lpnTransportador,
            'almacen_dest'      => $request->almacenDest,
            'ubicacion_dest'    => $request->ubicacionDest,
            'zone_id'           => !empty($zone) ? $zone->id : 67,
            'status'            => Pallets::RECEIVED
        ])->id;

        $skuArray = [];

        foreach ($request->detalleTransportador as $k => $data) {
            try {
                $skuArray[] = $data['sku'];
                $pickedBoxes += $data['cajas'];
                $pickedPieces += $data['cantidad'];
                PalletContent::create([
                    'pallet_id'     => $id,
                    'wave_id'       => $request->codigoOla,
                    'folio_mov'     => $data['folioMov'],
                    'sku'           => $data['sku'],
                    'cantidad'      => $data['cantidad'],
                    'cajas'         => $data['cajas'],
                    'variation_id'  => Redis::get('sku:'.$data['sku'].':id'),
                    'department_id' => Redis::get('sku:'.$data['sku'].':department'),
                    'style_id'      => Redis::get('sku:'.$data['sku'].':style')
                ]);
            } catch (Exception $e) {
                Log::error('Error en ' . __METHOD__ . ' linea ' . $e->getLine() . ':' . $e->getMessage());
                return ApiResponses::internalServerError($e);
            }
        }
        $wave->picking_start = $wave->picking_start == null ? new \DateTime : $wave->picking_start;
        $wave->picked_boxes = $pickedBoxes;
        $wave->picked_pieces = $pickedPieces;
        $wave->save();
        $result = array("exito"=> true,"message" => "Se ha recibido la info de manera correcta");
        return response($result, 200);
    }

    public function storeCapture(Request $request)
    {
        try {
            $order= $this->palletRepository->storeCapture($request);
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function palletsDispatched(Request $request)
    {
        try {
            $order= $this->palletRepository->palletsDispatched($request);
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function verifyZone(Request $request)
    {
        try {
            $order= $this->palletRepository->verifyZone($request);
            return ApiResponses::okObject($order);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param Request $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        try {
            $pallet = $this->palletRepository->getPallet($request, $id);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function showPalletZone(Request $request, $id)
    {
        try {
            $pallet = $this->palletRepository->getPalletByZone($request, $id);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function showPalletByIdZone(Request $request, $id)
    {
        try {
            $pallets = $this->palletRepository->getPalletZone($request, $id);
            return ApiResponses::okObject($pallets);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function showByWave(Request $request)
    {
        $object = Pallets::where('pallets.id', '=', $request->pallet_id)
            ->with('palletsSku')
            ->join('waves', 'pallets.wave_id', '=', 'waves.id')
            ->get();
        return ApiResponses::okObject($object);
    }

    public function showByStaging($staging_id)
    {
        try {
            $pallet = $this->palletRepository->getPalletByWave($staging_id);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param Pallets $pallets
     * @return Response
     */
    public function edit(Pallets $pallets)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Pallets $pallets
     * @return Response
     */
    public function update(Request $request, Pallets $pallets)
    {
        //
    }

    public function getNextNew(Request $request)
    {
        try {
            $wave = $request->wave;
            $palletCont = PalletContent::join('pallets', 'pallets.id', '=', 'pallet_contents.pallet_id')
                ->join('zones', 'zones.id', '=', 'pallets.zone_id')
                ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
                ->join('departments', 'styles.department_id', '=', 'departments.id')
                ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                ->select(
                    'pallet_contents.id',
                    'zones.id as zoneid',
                    'zones.code',
                    'pallets.id as palletsid',
                    'pallets.lpn_transportador',
                    'styles.style',
                    'departments.name',
                    DB::raw('SUM(pallet_contents.cantidad) as piezas'),
                    DB::raw('SUM(pallet_contents.cajas) as cajas'),
                    DB::raw('AVG(departments.ranking) as rank_dep'),
                    DB::raw('AVG(product_families.ranking) as rank_fam')
                )
                ->where('pallets.wave_id', $wave)
                ->where('pallets.status', '!=', Pallets::INDUCTION)
                ->groupBy('styles.id', 'pallets.id')
                ->orderBy('rank_dep', 'ASC')
                ->orderBy('piezas', 'DESC')
                ->orderBy('pallets.id')
                ->orderBy('styles.style')
                ->orderBy('zone_id')
                ->orderBy('rank_fam')
                ->limit(40)
                ->get()
                ->toArray();
            return ApiResponses::okObject($palletCont);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getNext(Request $request)
    {
        try {
            $wave = $request->wave;

            $pallet = Pallets::select('zones.id as zoneid', 'zones.code', 'pallets.id', 'pallets.lpn_transportador', DB::raw('AVG(departments.ranking) as rank_dep'), DB::raw('AVG(product_families.ranking) as rank_fam'))
                ->with(['palletsSku' => function ($q) {
                    $q->select('id', 'pallet_id', 'department_id', 'cantidad');
                    $q->with('department:id,name');
                }])
                ->join('zones', 'zones.id', '=', 'pallets.zone_id')
                ->join('waves', 'waves.id', '=', 'pallets.wave_id')
                ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
                ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
                ->join('departments', 'styles.department_id', '=', 'departments.id')
                ->join('product_families', 'styles.family_id', '=', 'product_families.id')
                ->where('waves.id', $wave)
                ->where('pallets.status', '!=', Pallets::INDUCTION)
                ->groupBy('pallets.id')
                ->orderBy('rank_dep', 'ASC')
                ->orderBy('styles.style')
                ->orderBy('pallet_contents.cantidad', 'DESC')
                ->orderBy('zone_id')
                ->orderBy('rank_fam')
                ->limit(10)
                ->get()
                ->toArray();

            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function induct(Request $request)
    {
        try {
            $pallet = $request->pallet;
            $mPallet = Pallets::where('lpn_transportador', $pallet)
            ->where('status', '!=', Pallets::INDUCTION)
            ->first();
            if (!empty($mPallet)) {
                $movement = new PalletMovement;
                $movement->user_id = Auth::id();
                $movement->session = Auth::user()->name;
                $movement->wave_id = $mPallet->wave_id;
                $movement->pallet_id = $mPallet->id;
                $movement->from_zone = $mPallet->zone ? $mPallet->zone->code : 'PICKING';
                $movement->to_zone = 'INDUCCION';
                $movement->save();
                $mPallet->zone_id = null;
                $mPallet->status = Pallets::INDUCTION;
                $mPallet->inducted_by = Auth::user()->name ?? null;
                $mPallet->save();
            } else {
                $mPallet = Pallets::where('lpn_transportador', $pallet)
                ->where('status', '=', Pallets::INDUCTION)
                ->first();
                if(!empty($mPallet)) {
                    return ApiResponses::notFound('El bin ya ha sido enviado a inducción.');
                }
                return ApiResponses::notFound('El BIN capturado no pudo ser encontrado.');
            }
            return ApiResponses::okObject($mPallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Pallets $pallets
     * @return Response
     */
    public function destroy(Pallets $pallets)
    {
        //
    }

    public function getListStaging(Request $request)
    {
        try {
            $pallet = $this->palletRepository->getListStaging($request->zonetype, $request->bin);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getListStagingAll(Request $request)
    {
        try {
            $pallet = $this->palletRepository->getZonesByZone($request->zonetype, $request->wave);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    /**
     * @param int $waveId
     * @return Response
     */
    public function getWavePallets($waveId)
    {
        try {
            $pallets = Pallets::where('wave_id', $waveId)
                        ->leftJoin('zones', 'zones.id', '=', 'pallets.zone_id')
                        ->select('pallets.*', 'zones.description')
                        ->get();
            return ApiResponses::okObject($pallets);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getPalletByLpn(Request $request)
    {
        try {
            $pallet = $this->palletRepository->getPalletByLpn($request);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getPalletsFromStaging(Request $request)
    {
        try {
            $pallet = $this->palletRepository->getPalletsFromStaging($request);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function storeMoving(Request $request)
    {
        try {
            $pallet = $this->palletRepository->storeMoving($request);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }


    public function getDetailNextPallet(Request $request)
    {
        try {
            $pallet = $request->pallet;
            $content = PalletContent::select('pallet_contents.id as pallet_content_id', DB::raw('sum(pallet_contents.cantidad) cantidad'), 'departments.name', 'styles.style')
                ->join('departments', 'departments.id', '=', 'pallet_contents.department_id')
                ->join('styles', 'styles.id', '=', 'pallet_contents.style_id')
                ->where('pallet_contents.pallet_id', '=', $pallet)
                ->groupBy('pallet_contents.style_id')
                ->orderBy('departments.ranking', 'ASC')
                ->orderBy('cantidad', 'desc')
                ->get()
                ->toArray();

            return ApiResponses::okObject($content);
        } catch (\Exception $e) {
            return ApiResponses::internalServerError($e);
        }
    }

    public function receivedPallet(Request $request)
    {
        try {
            $pallet = $this->palletRepository->changeStatusPallet($request);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getPalletMovements(Request $request)
    {
        try {
            $pallet = $this->palletRepository->getPalletMovements($request);
            return ApiResponses::okObject($pallet);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }
    }

    public function getWaveZones(Request $oRequest) {
        $validator = Validator::make($oRequest->all(), [
            'waveId' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return ApiResponses::badRequest($validator->errors()->first());
        }
        try {
            $wave = Wave::find($oRequest->waveId);
            $zones = [];
            foreach ($wave->zones as $zone) {
                $zones[] = $zone->description;
            }
            return ApiResponses::okObject($zones);
        } catch (Exception $e) {
            return ApiResponses::internalServerError($e->getMessage());
        }

    }
}
