<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartonLine extends Model
{

    public $fillable = [
        'carton_id',
        'line_id',
        'pieces',
        'prepacks',
        'prepacks_aud',
        'pieces_aud',
    ];

    public static $rules = [
        'carton_id'  => 'required|integer',
    ];

    public static $updateRules = [
        'carton_line_id' => 'required',
    ];

    public function carton()
    {
        return $this->belongsTo('App\Carton')->withDefault();
    }

    public function line()
    {
        return $this->belongsTo('App\Line')->withDefault();
    }
}
