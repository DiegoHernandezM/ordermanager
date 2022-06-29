<?php

Route::get('/divisions/{division}/departments', 'Api\DivisionController@departments')->name('divisions.departments');
Route::get('/divisions/all', 'Api\DivisionController@all')->name('divisions.all');
Route::get('/divisions/alldivisions', 'Api\DivisionController@allDivisions')->name('divisions.alldivisions');
Route::get('/divisions/getdepartments', 'Api\DivisionController@getDepartments')->name('divisions.getdepartments');
Route::get('/departments/all', 'Api\DepartmentController@index')->name('departments.index');
Route::get('/divisions/getDepartmentByDivisionId', 'Api\DivisionController@getDepartmentByDivisionId')->name('divisions.getDepartmentByDivisionId');
