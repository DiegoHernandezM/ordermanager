<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductFabric extends Model
{
    protected $table = 'product_fabrics';

    public $fillable = [
        'jdaId',
        'jdaName'
    ];

    public static $rules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
    ];

    public static $updateRules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
    ];
}
