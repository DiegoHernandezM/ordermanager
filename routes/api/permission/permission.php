<?php

Route::get('/permissions', 'Api\PermissionController@index')->name('permissions.index');
Route::get('/permissions/{id}/access', 'Api\PermissionController@edit')->name('permissions.edit');
Route::get('/permissions/getall', 'Api\PermissionController@show')->name('permissions.show');
Route::post('/permissions', 'Api\PermissionController@create')->name('permissions.create');
Route::post('/permissions/{id}', 'Api\PermissionController@update')->name('permissions.update');
Route::post('/permissions/{id}/delete', 'Api\PermissionController@delete')->name('permissions.delete');
