<?php

Route::get('/productclasses/all', 'Api\ClassesController@all')->name('productclasses.all');
Route::get('/productclasses/getclassesbydepartmentid', 'Api\ClassesController@getClassesByDepartmentId')->name('productclasses.getclassesbydepartmentid');
