<?php

Route::post('/pallets/staging/order', 'Api\PalletsController@generateOrder')->name('pallets.generateOrder');
Route::post('/pallets/staging/store', 'Api\PalletsController@storeCapture')->name('pallets.storeCapture');
Route::post('/pallets/staging/moving', 'Api\PalletsController@storeMoving')->name('pallets.storeMoving');
Route::post('/pallets/staging/dispatch', 'Api\PalletsController@palletsDispatched')->name('pallets.palletsDispatched');
Route::post('/pallets/staging/induct', 'Api\PalletsController@induct')->name('pallets.induct');
Route::get('/pallets/getwavespicking', 'Api\PalletsController@index')->name('pallets.index');
Route::get('/pallets/getinfobywave', 'Api\PalletsController@showByWave')->name('pallets.showByWave');
Route::get('/pallets/staging/getlist/', 'Api\PalletsController@getListStaging')->name('pallets.getListStaging');
Route::get('/pallets/staging/getliststaging/', 'Api\PalletsController@getListStagingAll')->name('pallets.getListStaging');
Route::post('/pallets/staging/verifyZone/', 'Api\PalletsController@verifyZone')->name('pallets.verifyZone');
Route::get('/pallets/staging/getpallets/', 'Api\PalletsController@getPalletsOrder')->name('pallets.getPalletsOrder');
Route::get('/pallets/staging/manager/', 'Api\PalletsController@getPalletsFromStaging')->name('pallets.getPalletsFromStaging');
Route::get('/pallets/staging/pallet/', 'Api\PalletsController@getPalletByLpn')->name('pallets.getPalletByLpn');
Route::get('/pallets/staging/buffer/', 'Api\PalletsController@getPalletsBuffer')->name('pallets.getPalletsBuffer');
Route::get('/pallets/staging/{id}', 'Api\PalletsController@showByStaging')->name('pallets.showByStaging');
Route::get('/pallets/getnext', 'Api\PalletsController@getNext')->name('pallets.getNext');
Route::get('/pallets/getnextnew', 'Api\PalletsController@getNextNew')->name('pallets.getNextNew');
Route::get('/pallets/zone/{id}', 'Api\PalletsController@showPalletZone')->name('pallets.showpalletzone');
Route::get('/pallets/getbyzone/{id}', 'Api\PalletsController@showPalletByIdZone')->name('pallets.showpalletbyzone');
Route::get('/pallets/details/detailnextpallet', 'Api\PalletsController@getDetailNextPallet')->name('pallets.getDetailNextPallet');
Route::post('/pallets/receivedpallet', 'Api\PalletsController@receivedPallet')->name('pallets.received');
Route::get('/pallets/wave/{id}', 'Api\PalletsController@getWavePallets')->name('pallets.getwavepallets');
Route::get('/pallets/getpalletmovements/{pallet}', 'Api\PalletsController@getPalletMovements')->name('pallets.getpalletmovement');
Route::get('/pallets/getwavezones', 'Api\PalletsController@getWaveZones')->name('pallets.getwavezones');
Route::get('/pallets/{id}', 'Api\PalletsController@show')->name('pallets.show');
