<?php

use Illuminate\Database\Seeder;

class AccessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $name = ['ALL','Roles','Permisos','Usuarios'];
        $key = ['*','AP_OCI&I','AP_OCI&I','AP_RDTDI'];
        foreach ($name as $k=>$valor) {
            DB::table('access_types')->insert([
                'application_name' => $valor,
                'applicationKey' => $key[$k]
            ]);
        }
    }
}
