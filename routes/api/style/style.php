<?php

Route::post('/styles/create', 'Api\StyleController@create')->name('styles.create');
Route::get('/styles/getstyles', 'Api\StyleController@get')->name('styles.getstyles');

