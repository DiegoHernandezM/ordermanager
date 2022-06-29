<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\ProductFamily::class, function (Faker $faker) {
    return [
        'jdaId'     => $faker->randomDigit,
        'jdaName'   => 'TEST',
        'label'     => 'TEST',
        'ranking'   => $faker->randomDigitNotNull
    ];
});
