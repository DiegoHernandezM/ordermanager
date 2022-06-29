<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PalletMovement extends Model
{
    //
    public $fillable = [
        'session',
        'wave_id',
        'pallet_id',
        'zone_type_id',
        'user_id',
        'cant',
        'sku'
    ];
}
