<?php
Route::get('/team/all', 'Api\TeamController@getAllTeams')->name('team.all');
Route::get('/team/{id}', 'Api\TeamController@index')->name('team.division');
Route::post('/team/create', 'Api\TeamController@store')->name('team.create');
Route::get('/team/show/{id}', 'Api\TeamController@show')->name('team.show');
Route::post('/team/update/{id}', 'Api\TeamController@update')->name('team.update');
Route::get('/team/delete/{id}', 'Api\TeamController@destroy')->name('team.delete');
Route::get('/team/departments/free', 'Api\TeamController@getDepartmentsFree')->name('team.departments');