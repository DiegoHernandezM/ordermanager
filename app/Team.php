<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';

    public $fillable = [
        'name',
        'description',
        'id_administrator',
        'id_department',
    ];

    public static $rules = [
        'name'          => 'required|min:3',
        'description'   => 'min:3',
        'id_administrator'   => 'required|numeric',
        'id_department'   => 'required|numeric',
    ];

    public function administrator() {
        return $this->hasOne('App\User','id_administrator','id');
    }

    public function department() {
        return $this->belongsTo('App\Department', 'id_department');
    }
}