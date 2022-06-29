<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemakeCartonsReportView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW cartons_report 
            as
            SELECT
                c.created_at fecha_creacion,
                og.id grupo,
                og.reference orden_surtido,
                c.wave_id ola,
                c.area,
                c.barcode boxId,
                c.transferNum transferencia,
                c.store tienda,
                cl.sku,
                cl.style estilo,
                cl.prepacks,
                cl.pieces piezas
            FROM carton_lines cl
                join cartons c on c.id = cl.carton_id                
                join orders o on o.id = c.order_id
                join order_groups og on og.id = o.order_group_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            CREATE OR REPLACE VIEW cartons_report 
            as
            SELECT
                c.created_at fecha_creacion,
                og.id grupo,
                og.reference orden_surtido,
                c.wave_id ola,
                c.barcode boxId,
                c.transferNum transferencia,
                cl.sku,
                cl.style estilo,
                cl.prepacks,
                cl.pieces piezas
            FROM carton_lines cl
                join cartons c on c.id = cl.carton_id                
                join orders o on o.id = c.order_id
                join order_groups og on og.id = o.order_group_id
        ");
    }
}
