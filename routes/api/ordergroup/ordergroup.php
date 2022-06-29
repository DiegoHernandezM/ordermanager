<?php

Route::get('/ordergroups/getcurrentweek', 'Api\OrderGroupController@getCurrentWeekOrderGroups')->name('ordergroups.getcurrentweek');
Route::get('/ordergroups/getlines', 'Api\OrderGroupController@getLinesFiltered')->name('ordergroups.getlines');
Route::get('/ordergroups/getskudetail', 'Api\OrderGroupController@getOrderGroupSkuDetail')->name('ordergroups.getskudetail');
Route::get('/ordergroups/getexcel', 'Api\OrderGroupController@getOrderGroupExcel')->name('ordergroups.getexcel');
Route::get('/ordergroups/getroutes', 'Api\OrderGroupController@getRoutesWithOrders')->name('ordergroups.routesandorders');
Route::get('/ordergroups/getdepartmentsorder/{id}', 'Api\OrderGroupController@getDeparmentsOrder')->name('ordergroups.getdepartmentsorder');
Route::get('/ordergroups/getdetailorder/{id}', 'Api\OrderGroupController@getDetailsOrder')->name('ordergroups.getdetailorder');
Route::get('/ordergroups/recalculate/{id}', 'Api\OrderGroupController@recalculate')->name('ordergroups.recalculate');
Route::get('/ordergroups/getdataordergroup{id?}{date?}', 'Api\OrderGroupController@getDataOrderGroup')->name('ordergroups.getdataordergroup');
Route::get('/ordergroups/getstores', 'Api\OrderGroupController@getStoresByOrderGroup')->name('ordergroups.getstores');
Route::post('/ordergroups/updateorder', 'Api\OrderGroupController@updateOrderStore')->name('ordergroups.updateorder');
Route::get('/ordergroups/getcartonsreport', 'Api\OrderGroupController@getCartonsReport')->name('ordergroups.getcartonsreport');
Route::get('/ordergroups/getordergroupsummary', 'Api\OrderGroupController@getOrderGroupSummary')->name('ordergroups.getordergroupsummary');
Route::post('/ordergroups/updateslots', 'Api\OrderGroupController@updateOrderSlots')->name('ordergroups.updateslots');
Route::post('/ordergroups/updateorderinstore', 'Api\OrderGroupController@updateOrderInStore')->name('ordergroups.updateOrderInStore');
