<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DevtrfContent extends Model
{
    public $fillable = [
        'devtrf_id',
        'sku',
        'variation_id',
        'pieces',
        'prepacks',
    ];

    public static $rules = [
        'sku'      => 'required',
        'pieces'    => 'required',
    ];

    public function devtrf()
    {
        return $this->belongsTo('App\Devtrf')->withDefault();
    }
}
