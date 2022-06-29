<?php

namespace App\Repositories;

use App\Http\Controllers\ApiResponses;
use App\PalletContent;
use App\PalletMovement;
use App\Pallets;
use App\Wave;
use App\Zone;
use Illuminate\Support\Facades\DB;
use Auth;

class PalletRepository
{
    protected $mPalletContent;
    protected $mZone;
    protected $mPallet;

    public function __construct()
    {
        $this->mPalletContent = new PalletContent();
        $this->mZone = new Zone();
        $this->mPallet = new Pallets();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPallet($oRequest, $id)
    {
        $pallets = $this->mPalletContent->where('pallet_id', $id)
            ->with('department')
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id')
            ->select('pallet_contents.*', 'product_families.jdaName')
            ->get();
        // foreach ($pallets as $key => $pallet) {
        //     $pallet->jdaName = substr($pallet->jdaName, 4);
        //     $pallet->department['name'] =  preg_replace('/[0-9]+/', '', $pallet->department['name']);
        // }
        $pallets = collect($pallets)->paginate(15);

        return $pallets;
    }

    /**
     * @param $oRequest
     * @param $idZone
     * @return mixed
     */
    public function getPalletByZone($oRequest, $idZone)
    {
        $pallets = $this->mZone->where('id', $idZone)
            ->with('pallets')
            ->with('pallets.zone:id,code')
            ->paginate(15);

        return $pallets;
    }

    /**
     * @param $oRequest
     * @param $idZone
     * @return mixed
     */
    public function getPalletZone($oRequest, $idZone)
    {
        $sFiltro = $oRequest->input('search', false);
        $paginate = (bool)$oRequest->pagination;
        if($paginate !== false) {
            $aPallets = $this->mPallet
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('lpn_transportador', 'like', "%$sFiltro%")
                                ->orWhere('almacen_dest', '=', "$sFiltro");
                        }
                    }
                )
                ->join('waves', 'pallets.wave_id', '=', 'waves.id')
                ->whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
                ->where('pallets.zone_id', $idZone)
                ->select('pallets.id as pallet_id', 'pallets.wave_id', 'pallets.lpn_transportador', 'waves.*', 'pallets.assignated_by', 'pallets.almacen_dest', 'pallets.inducted_by')
                ->where('waves.id', $oRequest->wave)->get();
        } else {
            $aPallets = $this->mPallet
                ->where(
                    function ($q) use ($sFiltro) {
                        if ($sFiltro !== false) {
                            return $q
                                ->orWhere('lpn_transportador', 'like', "%$sFiltro%")
                                ->orWhere('almacen_dest', '=', "$sFiltro");
                        }
                    }
                )
                ->join('waves', 'pallets.wave_id', '=', 'waves.id')
                ->whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
                ->where('pallets.zone_id', $idZone)
                ->select('pallets.id as pallet_id', 'pallets.wave_id', 'pallets.lpn_transportador', 'waves.*', 'pallets.assignated_by', 'pallets.almacen_dest', 'pallets.inducted_by')
                ->where('waves.id', $oRequest->wave)
                ->paginate((int) $oRequest->input('per_page', 20));
        }

         // dd($aPallets);
        return $aPallets;
    }


    /**
     * @return array
     */
    public function getAllPallets()
    {
        $select = [
            'waves.wave_ref',
            'departments.name',
            'departments.division_id',
            'variations.department_id',
            'pallet_contents.pallet_id',
            'pallets.lpn_transportador',
            DB::raw('divisions.name AS dept'),
            DB::raw('count(*) AS skus')
        ];

        $object = Wave::whereIn('waves.status', [Wave::PICKING, Wave::PICKED, Wave::SORTING])
            ->select($select)
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->join('variations', 'variations.id', '=', 'pallet_contents.variation_id')
            ->join('departments', 'variations.department_id', '=', 'departments.id')
            ->join('divisions', 'departments.division_id', '=', 'divisions.id')
            ->groupBy(
                'waves.wave_ref',
                'pallet_contents.pallet_id',
                'pallets.lpn_transportador',
                'variations.department_id',
                'departments.name',
                'departments.division_id',
                'divisions.name'
            )
            ->get()->toArray();

        $departments= [[
            'department_id'=>0,
            'dept'=>'TODOS'
        ]];
        $buffer = [];
        foreach ($object as $waves) {
            if (!in_array($waves['division_id'], $buffer)) {
                $buffer[]=$waves['division_id'];
                $departments[]=[
                    'department_id'=>$waves['department_id'],
                    'dept'=>$waves['dept']
                ];
            }
        }

        return ['headers'=>$departments,'list'=>$object];
    }

    public function getPalletByWave($staging)
    {
        $select = [
            DB::raw('pallets.id AS id'),
            DB::raw('waves.id AS wave_id'),
            'zones.code',
            'wave_ref',
            'zone_id',
            'lpn_transportador',
            DB::raw('AVG(departments.ranking) AS rank_avg_dep'),
            DB::raw('AVG(product_families.ranking) AS rank_avg'),
            DB::raw('"gainsboro" AS color')
        ];

        $result = Wave::select($select)
            ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
            ->where('pallets.status', '=', Pallets::STAGING)
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('departments', 'pallet_contents.department_id', '=', 'departments.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id')
            ->groupBy('pallets.id', 'waves.id', 'wave_ref', 'lpn_transportador')
            ->orderBy('rank_avg_dep')
            ->orderBy('zone_id')
            ->orderBy('rank_avg')
            ->get()
            ->toArray();

        $obj=['data'=>$result,'total'=>count($result)];

        return $obj;
    }

    public function generateOrder($request)
    {
        $key        = $request->user()->token()->id;
        $wave_id    = $request->wave;
        $buffer     = $request->buffer;
        $origin     = $request->origin;
        $cant       = $request->cant;

        $selects = [
            DB::raw('pallets.id AS pallet_id'),
            DB::raw('pallets.zone_id'),
            DB::raw('waves.id AS wave_id'),
            'wave_ref',
            'lpn_transportador',
            DB::raw('AVG(departments.ranking) AS rank_avg_dep'),
            DB::raw('AVG(product_families.ranking) AS rank_avg'),
        ];

        $available = Wave::select($selects)
            ->where([
                ['waves.id',"=",$wave_id],
                ['pallets.status', '=', Pallets::RECEIVED]
            ])
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('departments', 'pallet_contents.department_id', '=', 'departments.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->where('zones.zone_type_id', '=', $origin)
            ->groupBy('pallets.id', 'waves.id', 'wave_ref', 'lpn_transportador')
            ->orderBy('rank_avg_dep')
            ->orderBy('zone_id')
            ->orderBy('rank_avg')
            ->orderBy('lpn_transportador')
            ->limit($cant)
        ->get()->toArray();
        if (count($available)==0) {
            return ['success'=>false,"msg"=>"No se encontraron pallets"];
        }
        $pallets = [];
        foreach ($available as $data) {
            PalletMovement::create([
                'session'       => $key,
                'wave_id'       => $data["wave_id"],
                'pallet_id'     => $data["pallet_id"],
                'zone_type_id'  => $buffer,
                'user_id'       => $request->user()->id,
                'cant'          => 0,
                'sku'           => ''
            ]);
            $pallets[]=$data["pallet_id"];
        }
        Pallets::whereIn('id', $pallets)->update(["status"=>Pallets::STAGING]);

        return ['success'=>true];
    }

    public function getListStaging($zoneType, $bin = null)
    {
        $whereBin = null;
        $whereZoneType = ['zones.zone_type_id' => $zoneType];
        if ($bin) {
            $whereBin = ['lpn_transportador' => $bin];
            $whereZoneType = null;
        }
        $wave = Pallets::whereHas('wave', function ($q) {
            $q->whereIn('status', [Wave::PICKING,Wave::SORTING,Wave::PICKED]);
        })
            ->select(
                'pallets.*',
                'waves.wave_ref',
                DB::raw('AVG(product_families.ranking) AS rank_avg'),
                DB::raw('AVG(departments.ranking) AS rank_avg_dep'),
                DB::raw('SUM(cajas) as cajas'),
                'departments.name',
                'zones.code',
                'zones.description',
                'zones.zone_type_id'
            )
            ->join('waves', 'pallets.wave_id', '=', 'waves.id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->join('pallet_contents', 'pallet_contents.pallet_id', '=', 'pallets.id')
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('departments', 'styles.department_id', '=', 'departments.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id')
            ->where($whereZoneType)
            ->where($whereBin)
            ->groupBy('pallets.id', 'waves.id', 'waves.wave_ref', 'pallets.lpn_transportador')
            ->orderBy('rank_avg_dep')
            ->orderBy('zone_id')
            ->orderBy('rank_avg')
            ->limit(20)
            ->get();
        return $wave;
    }

    /**
     * @param $zoneType
     * @return mixed
     */
    public function getZonesByZone($zoneType, $wave)
    {
        $whereWave = [];
        //dd($wave);
        //$wave === 0 ? 1 : $wave;
        if ($wave >= 0) {
        //    dd($wave);
            $whereWave = ['waves.id' => $wave];
        }
        //dd($whereWave);
        $zones = $this->mZone
        ->select(
            'zones.id',
            'zones.code',
            'zones.zone_type_id',
            DB::raw('AVG(departments.ranking) AS rank_avg_dep')
        )
        ->withCount(['pallets' => function ($q) use ($whereWave) {
                $q->join('waves', 'pallets.wave_id', '=', 'waves.id');
                $q->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED]);
                $q->where($whereWave);
        }])
        ->whereHas('pallets', function ($q) use ($whereWave) {
            $q->join('waves', 'pallets.wave_id', '=', 'waves.id');
            $q->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED]);
            $q->where($whereWave);
        })
        ->with(['pallets' => function ($q) use ($whereWave) {
            $q->getBaseQuery()->orders = null;
            $q->join('waves', 'pallets.wave_id', '=', 'waves.id');
            $q->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED]);
            $q->where($whereWave);
            $q->select('pallets.id', 'zone_id', 'wave_id');
            $q->with(['palletsSku' => function ($r) {
                $r->getBaseQuery()->orders = null;
                $r->join('departments', 'pallet_contents.department_id', '=', 'departments.id');
                $r->select(
                    DB::raw('substr(departments.name ,1,3) name_department'),
                    'pallet_contents.pallet_id'
                );
            }]);
        }])
        ->join('pallets', 'zones.id', '=', 'pallets.zone_id')
        ->join('pallet_contents', 'pallet_contents.pallet_id', '=', 'pallets.id')
        ->join('departments', 'pallet_contents.department_id', '=', 'departments.id')
        ->groupBy('zones.id')
        ->whereNotIn('zones.id', [67])
        ->orderBy('rank_avg_dep', 'asc')
        ->get();

        return $zones;
    }

    public function getPalletsOrder($request)
    {
        $select = [
        DB::raw("*"),
        'zones.code',
        DB::raw('"gainsboro" AS color'),
        DB::raw('false AS checked'),
        DB::raw('"" AS loc'),
        ];
        if ($request->search) {
            $obj= Wave::select($select)
            ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
            ->where('pallets.status', '=', Pallets::MOVING)
            ->where('pallets.lpn_transportador', '=', $request->search)
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->join('pallet_movements', 'pallets.id', '=', 'pallet_movements.pallet_id')
            ->join('zone_types', 'zone_types.id', '=', 'pallet_movements.zone_type_id')
            ->get();
        } else {
            $obj= Wave::select($select)
            ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
            ->where('pallets.status', '=', Pallets::MOVING)
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->join('pallet_movements', 'pallets.id', '=', 'pallet_movements.pallet_id')
            ->join('zone_types', 'zone_types.id', '=', 'pallet_movements.zone_type_id')
            ->get();
        }

        return ["data"=>$obj];
    }

    public function storeCapture($request)
    {
        $lpn = [];
        foreach ($request->pallets as $data) {
            $lpn[]=$data["LPN"];
            $pallet = Pallets::where('lpn_transportador', '=', $data["LPN"])->first();
            $zoneModel = Zone::where("code", "=", $data["code"])->firstOrFail();
            $movement = new PalletMovement;
            $movement->user_id = Auth::id();
            $movement->session = Auth::user()->name;
            $movement->wave_id = $pallet->wave_id;
            $movement->pallet_id = $pallet->id;
            $movement->from_zone = $pallet->zone->code;
            $movement->to_zone = $zoneModel->code;
            $movement->save();
            $pallet->zone_id = $zoneModel->id;
            $pallet->save();
        }
        Pallets::whereIn('lpn_transportador', $lpn)->update(["status"=>Pallets::BUFFER]);
        return ["success"=>true];
    }

    public function verifyZone($request)
    {

        $zoneModel = Zone::where([["code", "=", $request->code]])->first();
        if (empty($zoneModel)) {
            return ["exists"=>false];
        } else {
            if ($request->has('bin')) {
                $bines = $request->bin;
                foreach ($bines as $bin) {
                    $pallet = Pallets::where('lpn_transportador', $bin['label'])->first();
                    $movement = new PalletMovement;
                    $movement->user_id = Auth::id();
                    $movement->session = Auth::user()->name;
                    $movement->wave_id = $pallet->wave_id;
                    $movement->pallet_id = $pallet->id;
                    $movement->from_zone = $pallet->zone->code;
                    $movement->to_zone = $zoneModel->code;
                    $movement->save();
                    $pallet->zone_id = $zoneModel->id;
                    $pallet->assignated_by = \Auth::user()->name ?? null;
                    $pallet->status = Pallets::STAGING;
                    $pallet->save();
                }
            }
            return ["exists"=>true,"occupied"=> false, "wave" => $pallet->wave_id];
        }
    }

    public function getPalletsBuffer($request)
    {
        $select = [
        DB::raw("*"),
        'zones.code',
        DB::raw('"gainsboro" AS color'),
        ];
        if ($request->zoneType) {
            $obj= Wave::select($select)
            ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->where('pallets.status', '=', Pallets::BUFFER)
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->join('pallet_movements', 'pallets.id', '=', 'pallet_movements.pallet_id')
            ->where('pallet_movements.zone_type_id', '=', $request->zoneType)
            ->get();
        } else {
            $obj= Wave::select($select)
            ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('zones', 'pallets.zone_id', '=', 'zones.id')
            ->where('pallets.status', '=', Pallets::BUFFER)
            ->join('pallet_movements', 'pallets.id', '=', 'pallet_movements.pallet_id')
            ->get();
        }
        return ["data"=>$obj];
    }

    public function palletsDispatched($request)
    {
        Pallets::whereIn('id', $request->pallets)->update(["status"=>Pallets::INDUCTION]);
        return ["success"=>true];
    }

    public function getPalletByLpn($request)
    {
        $obj=Pallets::with('palletsSku')
            ->where('lpn_transportador', "=", $request->lpn)->get();

        return ["data"=>$obj];
    }

    public function getPalletsFromStaging($request)
    {
        $select = [
        DB::raw("*"),
        'zones.code',
        DB::raw('"gainsboro" AS color'),
        DB::raw('false AS checked'),
        DB::raw('"" AS loc'),
        ];

        $obj= Wave::select($select)
        ->whereIn('waves.status', [Wave::PICKING,Wave::SORTING,Wave::PICKED])
        ->where('pallets.status', '=', Pallets::STAGING)
        ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
        ->join('zones', 'pallets.zone_id', '=', 'zones.id')
        ->join('pallet_movements', 'pallets.id', '=', 'pallet_movements.pallet_id')
        ->join('zone_types', 'zone_types.id', '=', 'pallet_movements.zone_type_id')
        ->get();

        return ["data"=>$obj];
    }

    public function storeMoving($request)
    {
        $lpn = [];
        foreach ($request->pallets as $data) {
            $lpn[]=$data["LPN"];
        }
        Pallets::whereIn('lpn_transportador', $lpn)->update(["status"=>Pallets::MOVING]);

        return ["success"=>true];
    }

    /**
     * @param $request
     * @return array
     */
    public function changeStatusPallet($request)
    {
        if ($request->bin != '' || $request->bin != null) {
            $pallet = Pallets::where('lpn_transportador', '=', $request->bin)
                ->where('zone_id', '!=', 0)
                ->first();

            if ($pallet) {
                $pallet->status = Pallets::MOVING;
                $pallet->assignated_by = \Auth::user()->name ?? null;
                $pallet->save();
                $wave = $pallet->wave;
                $pal = $wave->pallets->whereNotIn('zone_id', [0, 67])
                    ->sortByDesc('id')->first();
                $lastKnownLocation = '';
                if(!empty($pal)) {
                    $lastKnownLocation = substr(substr($pal->zone->description, 3), 0, -2);
                }
                $totalBoxes = Pallets::join('pallet_contents', 'pallet_contents.pallet_id', '=', 'pallets.id')
                    ->whereIn('pallets.status', [Pallets::MOVING, Pallets::STAGING, Pallets::INDUCTION])
                    ->where('pallets.wave_id', $pallet->wave_id)
                    ->sum('pallet_contents.cajas');

                $totalBines = Pallets::whereIn('status', [Pallets::MOVING, Pallets::STAGING, Pallets::INDUCTION])
                    ->where('wave_id', $pallet->wave_id)->count('id');
                $totalBinesReceive = Pallets::where('wave_id', $pallet->wave_id)->count('id');

                $data = [
                        'totalBoxes' => $totalBoxes,
                        'pickedBoxes' => $wave->picked_boxes,
                        'totalBines' => $totalBines,
                        'totalBinesReceive' => $totalBinesReceive,
                        'waveId' => $pallet->wave_id,
                        'lastKnownLocation' => $lastKnownLocation,
                        'zones' => $wave->zones
                    ];

                return $data;
            } else {
                $data = [
                    'totalBoxes' => 0,
                    'pickedBoxes' => 0,
                    'totalBines' => 0,
                    'totalBinesReceive' => 0,
                    'waveId' => 0,
                    'lastKnownLocation' => '',
                    'zones' => []
                ];

                return $data;
            }
        }
    }

    public function getPalletMovements($oRequest) {
        $aPallets = PalletMovement::where('pallet_id', $oRequest->pallet)->get();
        return $aPallets;
    }
}
