<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(App\Carton::class, function (Faker $faker) {
    return [
        'order_id'  => $faker->numberBetween(1, 100),
        'wave_id'   => $faker->numberBetween(1, 100),
        'total_pieces'  => $faker->numberBetween(100, 10000),
        'transferNum'   => $faker->numberBetween(88888888, 99999999),
        'transferStatus'    => 1,
        'waveNumber'    => $faker->numberBetween(1, 100),
        'businessName'  => 'Comercializadora Almacenes Garcia SA de CV',
        'area'  => 'SORTER'.$faker->numberBetween(1, 3),
        'orderNumber'   => $faker->numberBetween(1, 99999999),
        'barcode'   => 'C-'.$faker->numberBetween(10000000000, 99999999999),
        'route' => 0,
        'route_name'    => 'TEST',
        'store' => 0,
        'store_name'    => 'STORE-TEST',
        'labelDetail'   => json_encode('TEST'),
    ];
});
