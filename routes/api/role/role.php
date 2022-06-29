<?php

Route::get('/role', 'Api\RoleController@index')->name('role.index');
Route::get('/role/{id}', 'Api\RoleController@edit')->name('role.edit');
Route::get('/role/{id}/policies', 'Api\RoleController@showpolicies')->name('role.policies');
Route::post('/role', 'Api\RoleController@create')->name('role.create');
Route::post('/role/{id}', 'Api\RoleController@update')->name('role.update');
//Route::post('/role/update/{id}', 'Api\RoleController@update')->name('role.update');
Route::post('/role/delete/{id}', 'Api\RoleController@destroy')->name('role.destroy');
//Route::post('/role/asignpermissions/{id_rol}', 'Api\RoleController@addPermissions')->name('role.asignpermissions');