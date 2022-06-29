<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WavePriority extends Model
{
    protected $table = 'wave_priorities';

    public $fillable = [
        'name'
    ];
}
