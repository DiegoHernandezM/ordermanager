<?php

Route::get('/lines/find', 'Api\LineController@findLines')->name('lines.find');
Route::get('/lines/findtest', 'Api\LineController@findTest')->name('lines.findtest');
Route::get('/lines/get', 'Api\LineController@getLines')->name('lines.get');
Route::get('/lines/getall', 'Api\LineController@getAll')->name('lines.getall');
Route::get('/lines/getfromwave', 'Api\LineController@getFromWave')->name('lines.getfromwave');
Route::post('/lines/create', 'Api\LineController@create')->name('lines.create');
Route::patch('/lines/update/{id}', 'Api\LineController@update')->name('lines.update');
Route::delete('/lines/delete/{id}', 'Api\LineController@delete')->name('lines.delete');
Route::post('/lines/removepieces', 'Api\LineController@removePieces')->name('waves.removepieces');
