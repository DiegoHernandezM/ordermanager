<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreDepartment extends Model
{
    use SoftDeletes;

    public $fillable = [
        'store_id',
        'storeNumber',
        'department_id',
        'departmentNumber',
        'block_until',
        'user_id',
        'user_name'
    ];

    public static $rules = [
        'store_id'           => 'required|integer',
        'department_id'      => 'required|integer',
        'block_until'        => 'date|after:tomorrow'
    ];

    public static $rules_division = [
        'store_id'  => 'required|integer',
        'division'  => 'required|integer',
        'block_until' => 'date|after:tomorrow'
    ];

}
