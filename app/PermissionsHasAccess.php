<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionsHasAccess extends Model
{
    public $id_access;
    public $access;
    public $id_permission;
    public $fillable = [
        'id_permission',
        'id_access',
        'access',
    ];

    protected $table = 'permissions_has_accesses';


}
