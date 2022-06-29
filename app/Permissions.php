<?php

namespace App;

use Spatie\Permission\Models\Permission;

class Permissions extends Permission
{
    public function getAllPermission(){
        return $this->belongsTo(AccessType::class, 'access_type', 'id');
    }
}

/*
class Permissions extends Model
{
    //
    public $fillable = [

    ];

    public static $rules = [
        'number'      => 'required|integer',
        'name'        => 'required|string',
    ];

    public static $updateRules = [
        'store_id' => 'required',
    ];

    public function getAllPermissions(){
        return $this->belongsTo(Permission::class, 'access_type', 'id');
    }
}*/
