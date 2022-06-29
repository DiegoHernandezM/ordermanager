<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    $slots = $faker->numberBetween(1, 10);
    return [
        'store_id'       => 1,
        'slots'          => $slots,
        'active'         => true,
        'order_group_id'    => 1,
        'routeDescription'  => 'TEST',
        'routePriority'     => 0,
        'storeDescription'  => 'TEST',
        'storePriority'     => 999
    ];
});
