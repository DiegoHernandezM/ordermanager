<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Route;
use Faker\Generator as Faker;

$factory->define(Route::class, function (Faker $faker) {
    return [
        'name'         => 'RUTA'.$faker->numberBetween(1,9),
        'description' => 'DESCRIPTION TEST',
        'color'       => '#7a81ff',
    ];
});
