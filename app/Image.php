<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem as File;

class Image extends Model
{
    public $fillable = ['file', 'order', 'product_id', 'color_id'];

    public static $sizes = [
        'g' => [1597, 2048],
        'm' => [549, 732],
        'l' => [262, 336],
        'p' => [59, 75],
    ];

    public static $size_names = [
        'g' => 'large',
        'm' => 'medium',
        'l' => 'small',
        'p' => 'extrasmall',
    ];

    public function style()
    {
        return $this->belongsTo(Style::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public static $rules = [
        'fichero' => 'image',
        'file'    => 'required',
    ];

    public static $updateRules = [
        'fichero' => 'image',
    ];
}
