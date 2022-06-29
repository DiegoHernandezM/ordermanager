<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    public $fillable = [
        'zone_type_id',
        'pallet_id',
        'description',
        'code',
    ];

    public static $rules = [
        'code' => 'required',
    ];

    public static $updateRules = [
        'zone_type_id' => 'required',
    ];

    public function zonetype()
    {
        return $this->belongsTo('App\ZoneType', 'zone_type_id');
    }

    public function pallet()
    {
        return $this->belongsTo('App\Pallets', 'pallet_id')
            ->join('waves', 'pallets.wave_id', '=', 'waves.id')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id')
            ->join('styles', 'pallet_contents.style_id', '=', 'styles.id')
            ->join('product_families', 'styles.family_id', '=', 'product_families.id');
    }

    public function pallets()
    {
        return $this->hasMany('App\Pallets', 'zone_id', 'id');
    }

    public function palletsWithContent()
    {
        return $this->hasMany('App\Pallets', 'zone_id', 'id')
            ->join('pallet_contents', 'pallets.id', '=', 'pallet_contents.pallet_id');
    }
}
