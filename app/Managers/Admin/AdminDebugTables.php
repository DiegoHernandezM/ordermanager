<?php

namespace App\Managers\Admin;

use App\Carton;
use App\CartonLine;
use App\Line;
use App\Order;
use App\Pallets;
use App\PalletContent;
use Carbon\Carbon;
use Log;

class AdminDebugTables
{
    protected $mLines;
    protected $mOrders;
    protected $mCartons;
    protected $mCartonLines;

    public function __construct()
    {
        $this->mLines = new Line();
        $this->mCartonLines = new CartonLine();
        $this->mOrders = new Order();
        $this->mCartons = new Carton();
        $this->mPallets = new Pallets();
        $this->mPalletContents = new PalletContent();
    }

    /**
     * @return bool
     */
    public function deleteOldRecords()
    {
        try {
            $today = Carbon::now();
            $oldDate = $today->subDays(90);

            $lines = $this->mLines->where('updated_at', '<', $oldDate)->delete();
            $orders = $this->mOrders->where('updated_at', '<', $oldDate)->delete();
            $cartonLines = $this->mCartonLines->where('updated_at', '<', $oldDate)->delete();
            $cartons = $this->mCartons->where('updated_at', '<', $oldDate)->delete();
            $pallets = $this->mPallets->where('updated_at', '<', $oldDate)->delete();
            $palletContents = $this->mPalletContents->where('updated_at', '<', $oldDate)->delete();
            // TODO:: Eliminar pallets y pallets_contents
        } catch (\Exception $e) {
            Log::error('Error en '.__METHOD__.' línea '.$e->getLine().':'.$e->getMessage());
            return false;
        }
    }
}
