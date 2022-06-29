<?php

Route::get('/orders/get', 'Api\OrderController@get')->name('orders.get');
Route::get('/orders/getfromdivision', 'Api\OrderController@getFromDivision')->name('orders.getFromDivision');
Route::get('/orders/importcsv', 'Api\OrderController@importOrders')->name('orders.import');
Route::post('/orders/create', 'Api\OrderController@create')->name('orders.create');
Route::post('/orders/createmany', 'Api\OrderController@createMany')->name('orders.createmany');
Route::post('/orders/update/{id}', 'Api\OrderController@update')->name('orders.update');
Route::delete('/orders/delete/{id}', 'Api\OrderController@delete')->name('orders.delete');
Route::post('/orders/mergeSupplyOrder', 'Api\OrderController@mergeSupplyOrders')->name('orders.mergesupplyorder');
