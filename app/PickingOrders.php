<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PickingOrders extends Model
{
    public $fillable = [
        'wave_id',
        'sku',
        'pieces',
        'boxes',
        'department_id',
        'location',
        'user_id',
        'real_pieces',
        'location',
        'status'
    ];

    public static $rules = [
        'wave_id'  => 'required|integer',
        'sku'      => 'required',
        'pieces'   => 'required',
        'department_id' => 'required',
    ];

    public static $updateRules = [

    ];

    public function wave()
    {
        return $this->belongsTo('App\Wave')->withDefault(true);
    }

    public function department()
    {
        return $this->belongsTo('App\Department')->withDefault(true);
    }

}
