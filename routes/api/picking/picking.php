<?php

Route::get('/picking/all', 'Api\PickingController@index')->name('picking.all');
Route::post('/picking/{wave}/wave', 'Api\PickingController@getPickingByWave')->name('picking.getPickingByWave');
Route::post('/picking/{id}/dept', 'Api\PickingController@getPickingByDept')->name('picking.getPickingByDept');

Route::post('/picking/{id}/test', 'Api\PickingController@forTestOnly')->name('picking.forTestOnly');
Route::post('/picking/{id}/show', 'Api\PickingController@show')->name('picking.show');
Route::post('/picking/{op}/change','Api\PickingController@changeStatus')->name('picking.changeStatus');
Route::post('/picking/picking','Api\PickingController@store')->name('picking.store');
Route::post('/picking/{wave}/progress','Api\PickingController@getProgress')->name('picking.getProgress');
