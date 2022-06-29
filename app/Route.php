<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    public $fillable = [
        'name',
        'description',
        'color',
        'priority'
    ];

    public static $rules = [
        'name'        => 'required|string',
    ];

    public static $updateRules = [
        'route_id' => 'required',
    ];

    public function stores()
    {
        return $this->hasMany('App\Store')->orderBy('ranking', 'ASC');
    }
}
