<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Store;
use Faker\Generator as Faker;

$factory->define(Store::class, function (Faker $faker) {
    return [
        'number'         => $faker->numberBetween(10000,11000),
        'ranking'        => $faker->numberBetween(1,1000),
        'name'           => 'Store Test-'. $faker->randomNumber(),
        'sorter_ranking' => $faker->numberBetween(1,1000),
        'route_id'       => $faker->numberBetween(1,18),
        'pbl_ranking'    => 0,
        'position'       => 0,
        'status'         => $faker->numberBetween(0,1),
    ];
});
