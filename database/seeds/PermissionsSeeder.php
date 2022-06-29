<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        \Artisan::call('cache:forget spatie.permission.cache');
        /*
            1   Full Access
            2   /olas
            3   /catalogs
            4   /staging
            5   /users
            6   /perfil
            7   /
         */

        /*
           1    Full Access
           2    /perfil
           3    /
           4    /olas
           5    /catalogs
           6    /staging
           7    /users
           8    /reports

        */

        $arrays = [
            [
                'name' => 'Full Access',
                'description' => 'Acceso a Todo'
            ],
            [
                'name' => '/perfil',
                'description' => 'Administrar Perfil'
            ],
            [
                'name' => '/',
                'description' => 'Acceso Home'
            ],
            [
                'name' => '/olas',
                'description' => 'Administrar Olas'
            ],
            [
                'name' => '/catalogs',

                'description' => 'Administrar Catalogos'
            ],
            [
                'name' => '/staging',
                'description' => 'Administrar Staging'
            ],
            [
                'name' => '/users',
                'description' => 'Administrar Usuarios'
            ],
            [
                'name' => '/reports',
                'description' => 'Acceso a Reportes'
            ],
            [
                'name' => '/audit',
                'description' => 'Acceso a Auditoria'
            ],
            [
                'name' => '/blocks',
                'description' => 'Acceso a bloqueos'
            ],
        ];

        foreach ($arrays as $valor) {
            Permission::create([
                'guard_name' => 'web',
                'name' => $valor['name'],
                'description' => $valor['description']
            ]);
        }
    }
}
