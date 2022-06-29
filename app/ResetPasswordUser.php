<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResetPasswordUser extends Model
{
    protected $table = 'password_resets';
    public $timestamps = false;

    public $fillable = [
        'email',
        'token',
        'created_at'
    ];

    public static $rules = [
        'email' => 'required|email',
        'token' => 'required',
        'created_at' => 'required'
    ];

    public static $updateRules = [
        'email' => 'required|email',
        'token' => 'required',
        'created_at' => 'required'
    ];
}
