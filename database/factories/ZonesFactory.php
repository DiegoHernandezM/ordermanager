<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Zone;
use Faker\Generator as Faker;


$factory->define(Zone::class, function (Faker $faker) {
    return [
        'zone_type_id' => 2,
        'pallet_id' => 1,
        'description' => 'Buffer S3',
        'code' => 'B-'.rand(1,99)
    ];
});
