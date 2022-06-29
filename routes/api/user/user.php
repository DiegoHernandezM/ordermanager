<?php

Route::get('users/all', 'Api\UserController@index')->name('users.get');
Route::post('login', 'Api\AuthController@login')->name('user.login');
Route::get('user/showlogued/', 'Api\UserController@show')->name('users.find');
Route::get('oauth/me/hasAuthority', 'Api\UserController@getAuthority')->name('users.getAuthority');
Route::get('oauth/me/{id}/full', 'Api\UserController@accessByUserId')->name('users.accessByUserId');
Route::get('user/{id}/authorities', 'Api\UserController@getAuthorities')->name('users.authorities');
Route::get('user/permission', 'Api\UserController@checkPermission')->name('users.permission');
Route::get('user/getpermissions', 'Api\UserController@getPermissions')->name('users.getpermissions');
Route::get('user/edit/{id}', 'Api\UserController@edit')->name('users.findId');
Route::post('user/create', 'Api\UserController@store')->name('user.create');
Route::post('user/update', 'Api\UserController@update')->name('user.update');
Route::get('user/delete/{id}', 'Api\UserController@destroy')->name('user.delete');
Route::post('user/changepassword', 'Api\UserController@changePassword')->name('user.changePassword');
Route::post('user/changepasswordadmin', 'Api\UserController@changePasswordByAdmin')->name('user.changePasswordByAdmin');
Route::get('users/roleusers/{id}', 'Api\UserController@getUsersRol')->name('users.roles');
