<?php

namespace App\Repositories;

use App\Http\Controllers\ApiResponses;
use App\Pallets;
use App\Style;
use App\Wave;
use App\Zone;
use App\ZoneType;
use Illuminate\Http\Request;

class ZoneRepository extends BaseRepository
{

    protected $model = 'App\Zone';
    protected $mZone;
    protected $mZoneType;
    protected $mWave;
    protected $mStyle;

    public function __construct()
    {
        $this->mZone = new Zone();
        $this->mZoneType = new ZoneType();
        $this->mWave = new Wave();
        $this->mStyle = new Style();
    }

    /**
     * Obtiene lista de tipos de zona.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllZoneTypes()
    {
        $zoneTypes = $this->mZoneType->all();
        return ApiResponses::okObject($zoneTypes);
    }

    /**
     * Obtener zonas de determinado tipo de zona.
     *
     * @return \Illuminate\Http\Response
     */
    public function getZones(Request $request)
    {
        if ($request->staging > 0) {
            $zonesStaging = $this->getZoneStaging($request);
            return ApiResponses::okObject($zonesStaging);
        }
        if ($request->paginated) {
            $zones = $this->mZone->where('zone_type_id', $request->zoneType)
                ->with('zonetype:id,name')
                ->withCount('pallets')
                ->with('pallets', function ($q) {
                    $q->whereHas('wave', function ($q) {
                        $q->whereIn('status', [Wave::PICKING, Wave::PICKED, Wave::SORTING]);
                    });
                    $q->select('id', 'wave_id', 'zone_id');
                })
                ->paginate((int) $request->input('per_page', 20));
        } else {
            $zones = $this->mZone->where('zone_type_id', $request->zoneType)
                ->with('zonetype:id,name')
                ->withCount('pallets')
                ->with('pallets:id,wave_id,zone_id')
                ->paginate();
        }
        return ApiResponses::okObject($zones);
    }

    public function getZoneStaging($oRequest)
    {
        $zones = $this->mZone->join('zone_types', 'zones.zone_type_id', '=', 'zone_types.id')
            ->join('pallets', 'zones.pallet_id', '=', 'pallets.id')
            ->join('waves', 'pallets.wave_id', '=', 'waves.id')
            ->where('zones.zone_type_id', $oRequest->zoneType)
            ->where('pallets.status', Pallets::STAGING)
            ->paginate((int) $oRequest->per_page);
        return $zones;
    }

    /**
     * Obtiene las referencias de la ola para cada zona
     * @param $oRequest
     * @return mixed
     */
    public function getWaves($oRequest)
    {
        $waves = $this->mWave
            ->join('pallets', 'waves.id', '=', 'pallets.wave_id')
            ->join('zones', 'zones.pallet_id', '=', 'pallets.id')
            ->where('zones.zone_type_id', $oRequest->zoneType)
            ->select('waves.wave_ref')
            ->distinct()
            ->get();
        return $waves;
    }

    /**
     * Obtiene los estylos de los skus de cada pallet
     * @param $oRequest
     * @return mixed
     */
    public function getSylesPallets($oRequest)
    {
        $styles = $this->mStyle->where('id', $oRequest->style)
            ->with('family')
            ->paginate((int) $oRequest->input('per_page', 15));
        return $styles;
    }
}
