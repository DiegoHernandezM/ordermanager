<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wave extends Model
{
    public $fillable = [
        'area',
        'wave_ref',
        'area_id',
        'order_group_id',
        'business_rules',
        'pieces',
        'boxes',
        'rounded_pieces',
        'rounded_boxes',
        'complete',
        'status',
        'sorted_pieces',
        'picked_pieces',
        'planned_pieces',
        'verified_stock',
        'description',
        'total_sku',
        'induction_start',
        'induction_end',
        'verify_slots',
        'order_slots',
        'available_skus',
        'priority_id',
        'picked_boxes',
        'sorted_boxes',
        'prepacks',
        'picking_end',
        'picking_start',
        'final'
    ];

    const CANCELLED = 0;
    const CREATED   = 1;
    const PICKING   = 2;
    const PICKED    = 3;
    const SORTING   = 4;
    const COMPLETED = 5;
    const PPK_ERROR = 9;

    const STATUS = [
        0 => 'CANCELLED',
        1 => 'CREATED',
        2 => 'PICKING',
        3 => 'PICKED',
        4 => 'SORTING',
        5 => 'COMPLETED',
        9 => 'PPK_ERROR'
    ];

    public static $rules = [
        'order_group_id' => 'required',
    ];

    public static $updateRules = [

    ];

    public static $completeSupplyRules = [
        'codigoOla' => 'required|integer',
        'completa'  => 'required'
    ];

    public function lines()
    {
        return $this->hasMany('App\Line');
    }

    public function cartons()
    {
        return $this->hasMany('App\Carton')->orderBy('id');
    }

    public function linesProgress()
    {

        return $this->hasMany('App\Line')
            ->select(
                'wave_id',
                'department_id as id',
                'department_id',
                'departments.name',
                DB::raw('CAST(SUM(pieces) AS INTEGER) AS pieces'),
                DB::raw('CAST(SUM(expected_pieces) AS INTEGER) AS expected_pieces')
            )
            ->join('departments', 'lines.department_id', '=', 'departments.id')
            ->groupBy('wave_id', 'department_id');
    }

    public function linesDetail()
    {
        $select = [
            'pallet_contents.wave_id',
            'departments.id',
            'departments.name',
            'departments.ranking',
            DB::raw('CAST(SUM(cantidad) AS INTEGER) AS pzas'),
            DB::raw('CAST(SUM(pallet_contents.cajas) AS INTEGER) AS cajas'),
            DB::raw('Count(distinct sku) AS skus')
        ];

        return $this->hasMany('App\PalletContent')
            ->select($select)
            ->join('departments', 'pallet_contents.department_id', '=', 'departments.id')
            ->groupBy('pallet_contents.wave_id', 'departments.id', 'departments.name');
    }

    public function linesSkuSeeder()
    {
        $select = [
            'wave_id',
            'sku',
            'variation_id',
            'department_id',
            'style_id',
            DB::raw('SUM(pieces) AS pzas'),
            DB::raw('Count(*) AS rpt')
        ];
        return $this->hasMany('App\Line')
            ->select($select)
            ->groupBy(['sku','wave_id','variation_id','department_id','style_id']);
    }

    public function palletDetail()
    {
        return $this->hasMany('App\Pallets')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id');
    }

    public function pickedSkus()
    {
        return $this->hasMany('App\PalletContent');
    }

    public function pallets()
    {
        return $this->hasMany('App\Pallets');
    }

    public function zones()
    {
        return $this->hasMany('App\Pallets')
                ->join('zones', 'pallets.zone_id', '=', 'zones.id')
                ->where('pallets.zone_id', '!=', 67)
                ->select('zones.description')
                ->groupBy('pallets.zone_id');
    }

    public function ordergroup()
    {
        return $this->belongsTo('App\OrderGroup', 'order_group_id');
    }

    /**
     * Get the resource logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Log', 'loggable');
    }

    public function reasons()
    {
        return $this->hasMany('App\ReasonCancel');
    }
}
