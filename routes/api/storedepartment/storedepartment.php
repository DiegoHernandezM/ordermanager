<?php
Route::get('/storedepartments/get', 'Api\StoreDepartmentsController@get')->name('storedepartment.get');
Route::post('/storedepartments/create', 'Api\StoreDepartmentsController@create')->name('storedepartment.create');
Route::post('/storedepartments/delete', 'Api\StoreDepartmentsController@delete')->name('storedepartment.delete');
