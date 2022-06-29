<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    const OG = 'order_group';
    const RW = 'report_waves';

    public $fillable = [
        'email',
        'name',
        'subscrited_to',
        'active',
    ];
}
