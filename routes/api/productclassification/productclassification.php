<?php

Route::get('/classifications/all', 'Api\ProductClassificationsController@all')->name('productclassifications.all');
Route::get('/classifications/getAll', 'Api\ProductClassificationsController@getAll')->name('productclassifications.getall');
