<?php

Route::get('/producttypes/all', 'Api\TypeController@all')->name('producttypes.all');
Route::get('/producttypes/gettypes', 'Api\TypeController@getTypesByClassesId')->name('producttypes.gettypes');
