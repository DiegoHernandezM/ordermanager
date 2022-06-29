<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    protected $table = "user_team";

    public $fillable = [
        'id_team',
        'id_operator',
    ];

    public static $rules = [
        'id_team'  => 'required|numeric',
        'id_operator'   => 'required|numeric'
    ];

    public function team() {
        return $this->belongsTo('App\Team','id_team');
    }

    public function operator() {
        return $this->belongsTo('App\User', 'id_operator');
    }
}
