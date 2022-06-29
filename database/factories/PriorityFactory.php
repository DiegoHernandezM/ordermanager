<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use Faker\Generator as Faker;

$factory->define(App\Priority::class, function (Faker $faker) {
    return [
        'label'  => 'TEST',
        'order' => 999
    ];
});
