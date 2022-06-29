<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Pallets;
use Faker\Generator as Faker;

$factory->define(Pallets::class, function (Faker $faker) {
    return [
        'fecha_mov' => $faker->dateTimeBetween('now', '+01 days')->format('Y-m-d h:m:s'),
        'lpn_transportador' => 'B0000'. $faker->numberBetween(1000,9999),
        'almacen_dest' => $faker->numberBetween(10,30),
        'ubicacion_dest' => $faker->numberBetween(10,30).'-'.'PISO',
        'status' => $faker->numberBetween(0,5),
        'wave_id' => $faker->numberBetween(1,100),
        'enqueue' => 0,
        'zone_id' => 67,
        'assignated_by' => $faker->name,
    ];
});
