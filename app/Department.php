<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public $fillable = [
        'name',
        'division_id',
        'ranking',
        'jda_id',
        'jda_name'
    ];
    public function division()
    {
        return $this->belongsTo('App\Division')->orderBy('id');
    }

    public function classes()
    {
        return $this->hasMany('App\ProductClasses')->orderBy('id');
    }
}
