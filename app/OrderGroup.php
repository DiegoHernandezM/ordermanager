<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderGroup extends Model
{
    public $fillable = [
        'description',
        'reference',
        'local',
        'allocation',
        'allocationgroup',
        'transferencia',
        'solicitudId',
        'claveOS'
    ];

    public static $orderRulesFromMerc = [
        'id'       => 'required|integer',
        'store'    => 'required|integer',
        'sku'      => 'required|integer',
        'pieces'   => 'required|integer',
        'prepacks' => 'required|integer',
        'ppk'      => 'required|integer',
    ];

    public static $orderGroupRulesFromMerc = [
        'local'     => 'required|integer',
        'orders'    => 'required|array',
    ];


    public static $orderGroupRulesFromAlloc = [
        'local'       => 'required|integer',
        'allocationGroupId' => 'required|integer',
        'allocations' => 'required|array',
        'allocations.*.allocationId' => 'required|integer',
        'allocations.*.store'    => 'required|integer',
        'allocations.*.contents' => 'required|array',
        'allocations.*.contents.*.sku'      => 'required|integer',
        'allocations.*.contents.*.pieces'   => 'required|integer',
        'allocations.*.contents.*.prepacks' => 'required|integer',
        'allocations.*.contents.*.ppk'      => 'required|integer'
    ];

    public static $currentWeekJson = [[
        "created_at",
        "order_group_id",
        "order_group",
        "reference",
        "local",
        "total_pieces",
        "total_in_wave",
        "total_pending",
        "divisions" => [[
            "id",
            "division",
            "pieces",
            "in_wave",
            "pending",
            "order_group"
        ]]
    ]];

    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /**
     * Get the resource logs.
     */
    public function logs()
    {
        return $this->morphMany('App\Log', 'loggable');
    }

    public function lines()
    {
        return $this->hasManyThrough(
            Line::class,
            Order::class
        );
    }
}
