<?php

Route::get('/cartons/get', 'Api\CartonController@getAll')->name('cartons.get');
Route::get('/cartons/getinone', 'Api\CartonController@getAllInOne')->name('cartons.get');
Route::get('/cartons/getcartons', 'Api\CartonController@getAllCartons')->name('cartons.getcartons');
Route::post('/cartons/create', 'Api\CartonController@create')->name('cartons.create');
Route::patch('/cartons/update/{id}', 'Api\CartonController@update')->name('cartons.update');
Route::delete('/cartons/delete/{id}', 'Api\CartonController@delete')->name('cartons.delete');
Route::get('/cartons/zpl/{id}', 'Api\CartonController@getZpl')->name('cartons.zpl');
Route::get('/cartons/find/{id}', 'Api\CartonController@getDetailCarton')->name('cartons.detail');
Route::get('/cartons/demo', 'Api\CartonController@demo')->name('cartons.demo');
Route::get('/cartons/resend', 'Api\CartonController@resendCartons')->name('cartons.resend');
Route::get('/cartons/manualsync', 'Api\CartonController@manualSync')->name('cartons.manualsync');
Route::get('/cartons/wave/{id}', 'Api\CartonController@getCartonsWave')->name('cartons.getcartonwave');
Route::get('/brands/get', 'Api\BrandController@getAll')->name('brands.get');
Route::get('/cartons/shipment/{id}', 'Api\CartonController@getDetailCartonShipmentStore')->name('cartons.shipment');
Route::get('/cartons/shipment/report/{id}', 'Api\CartonController@getReportShipment')->name('cartons.shipmentreport');
Route::get('/cartons/initaudit/{barcode}', 'Api\CartonController@initAudit')->name('cartons.initaudit');
Route::get('/cartons/endaudit/{barcode}', 'Api\CartonController@endAudit')->name('cartons.endaudit');
Route::get('/cartons/auditlist', 'Api\CartonController@auditList')->name('cartons.auditList');
Route::post('/cartons/auditcarton', 'Api\CartonController@auditCarton')->name('cartons.auditcarton');
Route::get('/cartons/contents/{barcode}', 'Api\CartonController@cartonContents')->name('cartons.cartoncontents');
Route::get('/cartons/findline/', 'Api\CartonController@findLineSku')->name('cartons.findlinesku');
Route::get('/cartons/content/', 'Api\CartonController@getCartonsContentWave')->name('cartons.content');
Route::post('/cartons/checkauditpass', 'Api\CartonController@checkAuditPass')->name('cartons.checkauditpass');
