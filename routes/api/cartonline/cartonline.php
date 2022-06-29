<?php


Route::post('/cartonline/create', 'Api\CartonLineController@create')->name('cartonline.create');
Route::patch('/cartonline/update/{id}', 'Api\CartonLineController@update')->name('cartonline.update');
Route::delete('/cartonline/delete/{id}', 'Api\CartonLineController@delete')->name('cartonline.delete');