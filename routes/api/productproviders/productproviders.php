<?php

Route::get('/productprovider/all', 'Api\ProductProviderController@index')->name('productprovider.all');
Route::get('/productprovider/search', 'Api\ProductProviderController@search')->name('productprovider.search');
