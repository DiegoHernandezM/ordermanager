<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Style extends Model
{
    public $fillable = [
        'id',
        'deleted',
        'style',
        'jdaDivision',
        'division_id',
        'jdaDepartment',
        'department_id',
        'jdaClass',
        'class_id',
        'jdaType',
        'type_id',
        'jdaClassification',
        'classification_id',
        'class_id',
        'jdaFamily',
        'family_id',
        'jdaBrand',
        'brand_id',
        'jdaProvider',
        'provider_id',
        'description',
        'satCode',
        'satUnit',
        'publicPrice',
        'originalPrice',
        'regularPrice',
        'publicUsdPrice',
        'publicQtzPrice',
        'cost',
        'active',
        'international',
    ];

    public static $rules = [
        'style'      => 'required',
    ];

    public function family()
    {
        return $this->belongsTo('App\ProductFamily', 'family_id')->withDefault(true);
    }

    public function division()
    {
        return $this->belongsTo('App\Division')->withDefault(true);
    }

    public function department()
    {
        return $this->belongsTo('App\Department')->withDefault(true);
    }

    public function classification()
    {
        return $this->belongsTo('App\ProductClassification', 'classification_id')->withDefault(true);
    }
}
