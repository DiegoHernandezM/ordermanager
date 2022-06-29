<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MatchOldPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Viejo orden
        /*
            1	Full Access
            2	/olas
            3	/catalogs
            4	/staging
            5	/users
            6	/perfil
            7	/
         */
        // Nuevo orden
        /*
           1	Full Access
           2	/perfil
           3	/
           4	/olas
           5	/catalogs
           6	/staging
           7	/users
           8    /reports
        */

        // Match de viejos permisos con nuevo acomodo 21/06/2021
        $matchOldPermissions = [
            1 => 1,
            2 => 4,
            3 => 5,
            4 => 6,
            5 => 7,
            6 => 2,
            7 => 3,
        ];
        $oldUserPer = [];
        $newUserPer = [];
        $users = App\User::with('permissions')->get();
        foreach ($users as $user) {
            $oldUserPer[$user->id] = $user['permissions'];
        }
        foreach ($oldUserPer as $id => $permission) {
            foreach ($permission as $per){
                $newUserPer[$id][] = $matchOldPermissions[$per->id];
            }
        }

        foreach ($newUserPer as $id => $per) {
            $user = App\User::find((int)$id);
            $delete = DB::table('model_has_permissions')->where('model_id', '=', $user->id);
            $delete->delete();
            foreach ($per as $policy) {
                $user->givePermissionTo(Permission::find($policy));
            }
        }
        $this->saveNewOrderPermissions();
    }

    /**
     * @return bool
     */
    public function saveNewOrderPermissions()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        \Artisan::call('cache:forget spatie.permission.cache');

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
        ];

        foreach ($arrays as $valor) {
            Permission::create([
                'guard_name' => 'web',
                'name' => $valor['name'],
                'description' => $valor['description']
            ]);
        }

        return true;
    }
}
