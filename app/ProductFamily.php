<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductFamily extends Model
{
    protected $table = 'product_families';

    public $fillable = [
        'jdaId',
        'jdaName',
        'label',
        'ranking'
    ];

    public static $rules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
        'label'  => 'required',
        'ranking'  => 'required',
    ];

    public static $updateRules = [
        'jdaId'  => 'required|numeric',
        'jdaName'  => 'required',
        'label'  => 'required',
        'ranking'  => 'required',
    ];
}
