<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Line;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Line::class, function (Faker $faker) {
    $pzs = $faker->numberBetween(100, 1000);
    $ppks = round($pzs/2);
    return [
        'variation_id' => 1,
        'order_id'     => 1,
        'pieces'       => $pzs,
        'prepacks'     => $ppks,
        'department'   => '',
        'provider'     => '',
        'barcode'      => '',
        'style'        => '',
        'sku'          => '',
        'description'  => '',
        'size'         => '',
        'style_id'   => '',
        'wave_id'      => null,
    ];
});
