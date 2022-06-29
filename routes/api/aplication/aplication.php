<?php

Route::get('/access/applications', 'Api\AccessTypeController@getAccessTypes')->name('accesstypes.getAccessTypes');
Route::get('/access/applications/{id}/actions', 'Api\AccessTypeController@show')->name('accesstypes.show');