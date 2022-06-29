<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    protected $table = 'product_colors';

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
