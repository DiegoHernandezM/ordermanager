<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PalletContent extends Model
{
    public $id;
    public $folio_mov;
    public $sku;
    public $cantidad;
    public $cajas;

    public $fillable = [
        'pallet_id',
        'wave_id',
        'folio_mov',
        'sku',
        'cantidad',
        'cajas',
        'variation_id',
        'department_id',
        'style_id'
    ];

    public function pallets(){
        return $this->belongsTo('App\Pallets');
    }

    public function department(){
        return $this->belongsTo('App\Department', 'department_id')->orderBy('id');
    }
}
