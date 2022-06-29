<?php

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ModelHasPermissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user=User::find(1);
        $user->givePermissionTo('Full Access');

        $superAdmin = DB::table("permissions")->where(["name"=>"Full Access","guard_name"=>"api"])->first("id");

        DB::table("permissions_has_accesses")->update(["id_permission" => 0]);

        $seeds = DB::table('access_types')
            ->join('application_types','access_types.id','=','application_types.access_type')
            ->get()->toArray();

        $cant = DB::table("permissions_has_accesses")->get()->toArray();
        if(count($cant)==0){
            foreach ($seeds as $values){
                DB::table('permissions_has_accesses')->insert([
                    'id_permission'=>0,
                    'id_access'=>$values->id,
                    'access'=>$values->applicationKey.':'.$values->actionKey
                ]);
            }
        }
        DB::table("permissions_has_accesses")->where("access","=","*:*")->update(["id_permission" => $superAdmin->id]);
    }
}
