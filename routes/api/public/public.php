<?php

Route::post('/login', 'Api\AuthController@login')->name('login.api');
Route::post('/register', 'Api\AuthController@register')->name('register.api');
Route::get('/checkrole', 'Api\AuthController@checkrole');
Route::post('/gettoken', 'Api\UserController@sendTokenResetPassword')->name('user.getToken');
Route::get('/validtoken/{token}', 'Api\UserController@validTokenPassword')->name('user.validToken');
Route::post('/resetpassword', 'Api\UserController@resetPassword')->name('user.resetPassword');
Route::get('/v1/getdata', 'Api\WaveController@getDataDashboard')->name('data');
Route::get('/v1/wave/today', 'Api\ReportController@getWavesToday')->name('report.wavetoday');
Route::get('/v1/wave/week', 'Api\ReportController@getWavesWeek')->name('report.waveweek');
Route::get('/v1/carton/today', 'Api\ReportController@getCartonsToday')->name('report.cartontoday');
Route::get('/v1/carton/week', 'Api\ReportController@getCartonsWeek')->name('report.cartonweek');
Route::post('/v1/subscritereport', 'Api\UserReportsController@store')->name('subscritereport');
Route::get('/v1/messagewavedestiny', 'Api\MessageItAppsController@sendWaveDestiny')->name('messagewavedestiny');
Route::get('/v1/messagedestinywave', 'Api\MessageItAppsController@sendDestinyWave')->name('messagedestinywave');
