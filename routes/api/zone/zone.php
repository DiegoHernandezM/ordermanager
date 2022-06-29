<?php

Route::get('/zones/getallzonetypes', 'Api\ZoneController@getAllZoneTypes')->name('zones.getzonetypes');
Route::get('/zones/getzones', 'Api\ZoneController@getZones')->name('zones.getzones');
//Route::get('/zones/{id}', 'Api\ZoneController@show')->name('zones.show');
Route::get('/zones/{id}/printsticker', 'Api\ZoneController@printSticker')->name('zones.printsticker');
Route::get('/zones/getnamewavezone', 'Api\ZoneController@getNameWaves')->name('zones.getnamewaves');
Route::get('/zones/getstylesbuffer', 'Api\ZoneController@getStylesBuffer')->name('zones.getstylesbuffer');

Route::post('/zones/createzone', 'Api\ZoneController@createZone')->name('zones.createzone');
Route::post('/zones/{id}/update', 'Api\ZoneController@update')->name('zones.update');
Route::delete('/zones/{id}', 'Api\ZoneController@delete')->name('zones.delete');
Route::get('/zones/{id}', 'Api\ZoneController@show')->name('zones.show');
