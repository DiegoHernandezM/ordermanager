<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    public $fillable = [
        'name',
        'processed_in',
        'jda_id',
        'jda_name'
    ];

    const SAALMA_ALIAS = [
        '99',
        '98',
        '97',
        '96',
        '95',
        '94',
        '93',
        '92',
        '91'
    ];
    const DIVISION_ALIAS = [
        'Mujer',
        'Hombre',
        'Bebes',
        'Infantiles',
        'Interiores',
        'Accesorios',
        'Perfumeria',
        'Escolar',
        'Otros'
    ];

    public function departments()
    {
        return $this->hasMany('App\Department')->orderBy('id');
    }
}
