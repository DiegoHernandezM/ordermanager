<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductClasses extends Model
{
    protected $table = 'classes';

    public $fillable = [
        'jdaId',
        'jdaName',
        'label'
    ];

    public static $rules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
    ];

    public static $updateRules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
    ];

    public function department()
    {
        return $this->belongsTo('App\Department');
    }

    public function types()
    {
        return $this->hasMany('App\ProductType', 'clasz_id');
    }
}
