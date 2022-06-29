<?php

Route::get('/getlogs/{barcode}', 'Api\LogPrintCartonController@getByBarcode')->name('logcarton.find');

