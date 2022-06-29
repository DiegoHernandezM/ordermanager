<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carton extends Model
{
    public $fillable = [
        'order_id',
        'wave_id',
        'total_pieces',
        'transferNum',
        'transferStatus',
        'waveNumber',
        'businessName',
        'area',
        'orderNumber',
        'barcode',
        'route',
        'route_name',
        'store',
        'store_name',
        'labelDetail',
        'pendingConfirmation',
        'audited_by',
        'audit_init',
        'audit_end',
        'authorized_by'
    ];

    public static $rules = [
        'orderNumber'      => 'required|integer',
        'waveNumber'       => 'required|integer',
        'businessName'     => 'min:3',
        'area'             => 'regex:/^[\pL\s\-]+$/u|min:3',
        'barcode'          => 'required|alpha_dash',
        'route'            => 'numeric',
        'route_name'       => 'min:3',
        'store'            => 'numeric',
        'store_name'       => 'min:3',
        'labelDetail'      => 'array',
    ];

    public static $updateRules = [
        'carton_id' => 'required',
    ];
    
    const SENDED  = 1;
    const HOLD    = 2;
    const AUDITED = 3;
    const AUDITSEND = 4;
    const COMPLETED = 5;

    public function order()
    {
        return $this->belongsTo('App\Order')->withDefault();
    }

    public function wave()
    {
        return $this->belongsTo('App\Wave')->withDefault();
    }

    public function cartonLines()
    {
        return $this->hasMany(CartonLine::class);
    }

    public function auditedBy()
    {
        return $this->belongsTo('App\User', 'audited_by')->withDefault();
    }

    public function authorizedBy()
    {
        return $this->belongsTo('App\User', 'authorized_by')->withDefault();
    }
}
