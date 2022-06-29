<?php

Route::get('/stores/all', 'Api\StoreController@index')->name('stores.all');
Route::get('/stores/allstores', 'Api\StoreController@getStores')->name('stores.getall');
Route::get('/stores/list', 'Api\StoreController@list')->name('stores.list');
Route::get('/stores/get', 'Api\StoreController@get')->name('stores.get');
Route::get('/stores/{id}', 'Api\StoreController@findStore')->name('stores.find');
Route::post('/stores/create', 'Api\StoreController@create')->name('stores.create');
Route::post('/stores/update', 'Api\StoreController@update')->name('stores.update');
Route::get('/stores/delete/{id}', 'Api\StoreController@delete')->name('stores.delete');
Route::post('/stores/updatedata', 'Api\StoreController@updateStore')->name('stores.updateStore');
Route::post('/stores/uranking', 'Api\StoreController@uRanking')->name('stores.uRanking');
