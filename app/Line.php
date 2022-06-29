<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    public $fillable = [
        'order_id',
        'division',
        'category',
        'classification',
        'sku',
        'pieces',
        'prepacks',
        'ppk',
        'variation_id',
        'style_id',
        'wave_id',
        'complete',
        'equivalent_boxes',
        'rounded_boxes',
        'expected_pieces',
        'division_id',
        'department_id',
        'ppksaalma',
        'updated_by',
        'priority'
    ];

    const STATUS_CREATED   = 0;
    const STATUS_FOUND     = 1;
    const STATUS_IN_BUFFER = 2;
    const STATUS_INDUCTED  = 3;

    public static $rules = [
        'order_id'      => 'required|integer',
        'sku'           => 'required|integer',
        'pieces'        => 'required|integer',
        'prepacks'       => 'required|integer',
    ];

    public static $updateRules = [

    ];

    public function order()
    {
        return $this->belongsTo('App\Order')->withDefault();
    }

    public function divisionModel()
    {
        return $this->belongsTo('App\Division', 'division_id')->withDefault();
    }

    public function style()
    {
        return $this->belongsTo('App\Style')->withDefault();
    }

    public function department()
    {
        return $this->belongsTo('App\Department')->withDefault();
    }

    public function cartonLine()
    {
        return $this->hasMany('App\CartonLine', 'line_id', 'id');
    }

    /**
     * Get the resource logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Log', 'loggable');
    }
}
