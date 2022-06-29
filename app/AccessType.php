<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class AccessType extends Model
{
    //
    public $fillable = [
        'name_access',
    ];

    public function getAllAccessType()
    {
        return $this->hasMany(Permission::class,'id','access_type');
    }
}
