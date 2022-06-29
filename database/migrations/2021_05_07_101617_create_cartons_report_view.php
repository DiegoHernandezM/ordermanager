<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartonsReportView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW cartons_report 
            as
            SELECT
                c.created_at fecha_creacion,
                og.id grupo,
                og.reference orden_surtido,
                c.wave_id ola,
                c.barcode boxId,
                c.area,
                c.transferNum transferencia,
                c.store tienda,
                c.shipment,
                cl.sku,
                cl.style estilo,
                cl.prepacks,
                cl.pieces piezas,
                cl.prepacks_aud,
                cl.pieces_aud,
                c.audited_by,
                u.name,
                ua.name autoriza,
                c.audit_init inicio_aud,
                c.audit_end fin_aud
            FROM carton_lines cl
                join cartons c on c.id = cl.carton_id
                join orders o on o.id = c.order_id
                join order_groups og on og.id = o.order_group_id
                left join users u on u.id = c.audited_by
                left join users ua on ua.id = c.authorized_by
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cartons_report');
    }
}
