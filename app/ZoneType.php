<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZoneType extends Model
{

    const BUFFERS1    = 1;
    const BUFFERS2    = 2;
    const BUFFERPBL   = 3;
    const INDUCTION   = 4;
    const STAGING     = 5;

    public $fillable = [
        'name',
    ];

    public static $rules = [
        'name' => 'required',
    ];

    public static $updateRules = [
        
    ];
}
