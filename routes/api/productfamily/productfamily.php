<?php
Route::get('/productfamily/all', 'Api\ProductFamilyController@index')->name('productfamily.all');
Route::get('/productfamily/getall', 'Api\ProductFamilyController@getAll')->name('productfamily.getall');
Route::get('/productfamily/{id}', 'Api\ProductFamilyController@show')->name('productfamily.find');
Route::post('/productfamily/update', 'Api\ProductFamilyController@update')->name('productfamily.update');
