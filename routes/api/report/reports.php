<?php
Route::get('/report/wave/today', 'Api\ReportController@getWavesToday')->name('report.wavetoday');
Route::get('/report/wave/week', 'Api\ReportController@getWavesWeek')->name('report.waveweek');
Route::get('/report/carton/today', 'Api\ReportController@getCartonsToday')->name('report.cartontoday');
Route::get('/report/carton/week', 'Api\ReportController@getCartonsWeek')->name('report.cartonweek');
Route::get('/report/wave/planned', 'Api\ReportController@getPlannedWaves')->name('report.plannedwaves');
Route::get('/report/waves', 'Api\ReportController@getWaveData')->name('report.wavedata');
Route::get('/report/waves/sort{dateInit?}/{dateEnd?}', 'Api\ReportController@getWaveDataWithParams')->name('report.wavedata.withparams');
Route::post('/report/ppk', 'Api\ReportController@validatePpk')->name('report.validateppk');
Route::get('/report/waves/orderreport','Api\ReportController@getWaveOrdersReport')->name('reportwave.finished');
Route::get('/report/shipmentsbyordergroup','Api\ReportController@getShipmentReportByOrderGroup')->name('reportwave.shipmentreport');
