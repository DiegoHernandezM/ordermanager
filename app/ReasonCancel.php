<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReasonCancel extends Model
{
    protected $table = 'catalogue_reasons_to_cancel_wave';

    public $fillable = [
        'reason',
    ];

    public function wave()
    {
        return $this->belongsTo('App\Wave');
    }
}
