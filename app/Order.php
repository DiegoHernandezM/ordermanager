<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $fillable = [
        'store_id',
        'ranking',
        'order_group_id',
        'slots',
        'label_data',
        'merc_id',
        'storeNumber',
        'routeNumber',
        'storeDescription',
        'routeDescription',
        'storePriority',
        'storePosition',
        'routePriority',
        'status',
        'allocation'
    ];

    public static $rules = [
        'lines'      => 'required|array'
    ];

    public static $updateRules = [];

    public function lines()
    {
        return $this->hasMany('App\Line')->orderBy('id');
    }

    public function contents()
    {
        return $this->hasMany('App\Line')->orderBy('id')->limit(4);
    }

    public function cartons()
    {
        return $this->hasMany('App\Carton')->orderBy('id');
    }

    public function ordergroup()
    {
        return $this->belongsTo('App\OrderGroup', 'order_group_id')->withDefault(true);
    }

    public function store()
    {
        return $this->belongsTo('App\Store')->withDefault(true);
    }
}
