<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variation extends Model
{

    public $fillable = [
        'id',
        'sku',
        'name',
        'color_id',
        'stock',
        'active',
        'created_at',
        'updated_at',
        'ppc',
        'ppk',
        'style_id',
        'jdaSize',
        'size_id',
        'jdaColor',
        'jdaPriority',
        'priority_id',
        'department_id',
        'division_id'
    ];

    public function style()
    {
        return $this->belongsTo('App\Style')->withDefault();
    }

    public function color()
    {
        return $this->belongsTo('App\Color')->withDefault();
    }

    public function lines()
    {
        return $this->hasMany('App\Line')->orderBy('id');
    }
}
