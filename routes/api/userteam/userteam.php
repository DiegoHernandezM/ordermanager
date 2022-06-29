<?php
Route::get('/userteam/all/{id}', 'Api\UserTeamController@index')->name('userteam.all');
Route::post('/userteam/create', 'Api\UserTeamController@store')->name('userteam.create');
Route::get('/userteam/show/{id}', 'Api\UserTeamController@show')->name('userteam.show');
Route::post('/userteam/update/{id}', 'Api\UserTeamController@update')->name('userteam.update');
Route::get('/userteam/delete/{id}', 'Api\UserTeamController@destroy')->name('userteam.delete');