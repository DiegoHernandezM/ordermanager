<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Division;
use Faker\Generator as Faker;

$factory->define(Division::class, function (Faker $faker) {
    return [
        'name'         => 'TEST - NAME',
        'processed_in' => 'TEST',
        'jda_id'       => $faker->numberBetween(1,9),
        'jda_name'     => '0'.$faker->numberBetween(1,9).' '. 'TEST-JDA-NAME',
    ];
});
