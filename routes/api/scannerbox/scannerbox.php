<?php

Route::get('/scannerbox/{barcode}', 'Api\ScannerBoxController@getInfoBarCode')->name('scannerbox.barcode');