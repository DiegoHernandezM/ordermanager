<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $table = 'priorities';

    public $fillable = [
        'label',
        'order',
        'jda_id',
        'jda_name'
    ];

    public static $rules = [
        'label'  => 'required|min:3',
        'order'  => 'required|numeric',
    ];

    public static $updateRules = [
        'label'  => 'required|min:3',
        'order'  => 'required|numeric',
    ];
}
