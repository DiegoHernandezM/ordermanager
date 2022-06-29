<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => ['json.response']], function () {

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    // public routes
    require base_path('routes/api/public/public.php');
    //login api
    // private routes
    Route::middleware('auth:api')->group(function () {
        Route::get('/logout', 'Api\AuthController@logout')->name('logout');
    });
});

Route::group(
    [
        'middleware' => ['auth:api'],
        'prefix'     => '/v1'
    ],
    function () {
        Route::get('/user', 'Api\UserController@show')->name('user.show');
        require base_path('routes/api/permission/permission.php');

        require base_path('routes/api/aplication/aplication.php');

        require base_path('routes/api/role/role.php');

        require base_path('routes/api/store/store.php');

        require base_path('routes/api/storedepartment/storedepartment.php');

        require base_path('routes/api/route/route.php');

        require base_path('routes/api/wave/wave.php');

        require base_path('routes/api/division/division.php');

        require base_path('routes/api/ordergroup/ordergroup.php');

        require base_path('routes/api/order/order.php');

        require base_path('routes/api/line/line.php');

        require base_path('routes/api/carton/carton.php');

        require base_path('routes/api/cartonline/cartonline.php');

        require base_path('routes/api/user/user.php');

        require base_path('routes/api/priority/priority.php');

        require base_path('routes/api/picking/picking.php');

        require base_path('routes/api/zone/zone.php');

        require base_path('routes/api/team/team.php');

        require base_path('routes/api/userteam/userteam.php');

        require base_path('routes/api/pallets/pallets.php');

        require base_path('routes/api/productclassification/productclassification.php');

        require base_path('routes/api/productclasses/productclasses.php');

        require base_path('routes/api/productfamily/productfamily.php');

        require base_path('routes/api/producttype/producttype.php');

        require base_path('routes/api/productproviders/productproviders.php');

        require base_path('routes/api/report/reports.php');

        require base_path('routes/api/userreports/userreport.php');

        require base_path('routes/api/logcarton/logcarton.php');

        require base_path('routes/api/brand/brand.php');

        require base_path('routes/api/style/style.php');

        require base_path('routes/api/simulations/simulations.php');
    }
);

Route::post('/user/token', 'Api\AuthController@login')->name('user.token');

Route::group(
    [
        'prefix'     => '/v1'
    ],
    function () {
        Route::get('/scannerbox/{barcode}', 'Api\ScannerBoxController@getInfoBarCode')->name('scannerbox.barcode');
    }
);

Route::group(
    [
        'middleware' => 'basicAuth',
        'prefix'     => '/v1'
    ],
    function () {
        Route::post('/ordergroup', 'Api\OrderGroupController@createOrderGroup')->name('ordergroup.createordergroup');
    }
);

Route::group(
    [
        'middleware' => 'basicAuthItapps',
        'prefix'     => '/v1'
    ],
    function () {
        Route::post('/transportador/', 'Api\PalletsController@store')->name('pallets.store');
        Route::post('/transferencia', 'Api\CartonController@setTransfer')->name('cartons.settransfer');
        Route::post('/embarque', 'Api\CartonController@setShipment')->name('cartons.setshipment');
        Route::post('/cierreola/', 'Api\WaveController@completeSupply')->name('pallets.completesupply');
        Route::post('/allocationgroup', 'Api\OrderGroupController@createAllocationGroup')->name('ordergroup.createallocationgroup');
    }
);
