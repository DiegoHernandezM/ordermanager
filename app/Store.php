<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public $fillable = [
        'number',
        'name',
        'ranking',
        'route_id',
        'pbl_ranking',
        'sorter_ranking',
        'status',
        'rmsId',
        'rmsName'
    ];

    public static $rules = [
        'number'      => 'required|integer',
        'name'        => 'required|string',
        'rmsId'       => 'integer',
        'rmsName'     => 'string|max:80'
    ];

    public static $updateRules = [
        'store_id' => 'required',
    ];

    public function route()
    {
        return $this->belongsTo('App\Route')->withDefault();
    }

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    public function cartons()
    {
        return $this->hasManyThrough(
            Carton::class,
            Order::class,
            'store_id',
            'order_id',
        );
    }
}
