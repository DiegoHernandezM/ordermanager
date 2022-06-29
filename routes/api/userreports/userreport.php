<?php

Route::get('ureport/all', 'Api\UserReportsController@get')->name('ureport.get');
Route::post('ureport/save', 'Api\UserReportsController@store')->name('ureport.save');
Route::get('ureport/delete/{id}', 'Api\UserReportsController@desactivateUser')->name('ureport.delete');
Route::get('ureport/edit/{id}', 'Api\UserReportsController@edit')->name('ureport.edit');
Route::post('ureport/update', 'Api\UserReportsController@update')->name('ureport.update');
