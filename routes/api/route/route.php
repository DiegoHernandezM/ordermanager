<?php

Route::get('/routes/getall', 'Api\RouteController@getAll')->name('routes.getall');
Route::get('/routes/getallroutes', 'Api\RouteController@getAllRoutes')->name('routes.getallroutes');
Route::get('/routes/{id}', 'Api\RouteController@getRoute')->name('routes.getroute');
Route::post('/routes/create', 'Api\RouteController@create')->name('routes.create');
Route::post('/routes/update', 'Api\RouteController@update')->name('routes.update');
Route::get('/routes/delete/{id}', 'Api\RouteController@delete')->name('routes.delete');
