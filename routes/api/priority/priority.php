<?php

Route::get('/priority/all', 'Api\PriorityController@index')->name('priority.index');
Route::get('/priority/allpriorities', 'Api\PriorityController@all')->name('priority.all');
Route::post('/priority/create', 'Api\PriorityController@store')->name('priority.store');
Route::get('/priority/show/{id}', 'Api\PriorityController@show')->name('priority.show');
Route::post('/priority/update', 'Api\PriorityController@update')->name('priority.update');
Route::get('/priority/delete/{id}', 'Api\PriorityController@destroy')->name('priority.delete');
