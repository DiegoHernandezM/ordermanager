<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    const LOG_SYSTEM   = 1;
    const LOG_WAMAS    = 2;
    const LOG_SAALMA   = 3;
    const LOG_SQS      = 4;


    const TYPE_LOG = array(
        1 => 'WamasRejection',
        2 => 'WamasWaveAck',
        3 => 'WamasOrderFinished',
        4 => 'WamasCarton',
        5 => 'WamasWaveFinished',
    );


    public $fillable = [
        'id',
        'message',
        'loggable_id',
        'loggable_type',
        'user_id',
    ];

    /**
     * Get the owning loggable model.
     */
    public function loggable()
    {
        return $this->morphTo();
    }
}
