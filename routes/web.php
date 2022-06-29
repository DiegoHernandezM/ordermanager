<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Managers\SendMailsManager;
use App\ResetPasswordUser;
use App\User;
use Carbon\Carbon;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/validatetoken/{token}', function ($token) {
    $isValidToken = ResetPasswordUser::where('token', $token)->whereDate('created_at', Carbon::today())->first();
    if (!empty($isValidToken)) {
        $newPassword = Str::random(12);
        $u = User::where('email', $isValidToken->email)->first();
        $u->update(['password' => Hash::make($newPassword)]);
        $m = new SendMailsManager;
        $m->newUserMail($u->name, $u->email, $newPassword);
        ResetPasswordUser::where('token', $token)->whereDate('created_at', Carbon::today())->delete();
        return view('resetting');
    } else {
        return view('invalidtoken');
    }
});
