<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devtrf extends Model
{
    public $fillable = [
        'devolution_id',
        'total_pieces',
        'total_prepacks',
        'transferNum',
        'store',
    ];

    public static $rules = [
        'transferNum'      => 'required',
        'store'            => 'required',
    ];

    public function contents()
    {
        return $this->hasMany(DevtrfContent::class);
    }
}
