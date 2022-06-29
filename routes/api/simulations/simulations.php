<?php

Route::post('/sqs/message', 'Api\SqsLocalController@sendMessage')->name('sqs.send');
