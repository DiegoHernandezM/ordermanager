<?php

use Illuminate\Database\Seeder;

class ApplicationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $actionKey = ['*','AC_GEOLL', 'AC_OPIOT', 'AC_OOCAR', 'AC_COAAU', 'AC_DELTY', 'AC_LLSEL', 'AC_GEDOT', 'AC_YSLGG',
            'AC_ELOET', 'AC_RUDPU', 'AC_ESEEU', 'AC_SRAGL', 'AC_EERST', 'AC_PPESS', 'AC_DREUE', 'AC_TEETD', 'AC_BRSIE',
            'AC_TABTA','AC_EEBIO','AC_OLREE'];

        $action_name = ['ALL', 'GetAllPolicies', 'GetPolicyById', 'CreatePolicy', 'UpdatePolicy', 'DeletePolicy',
            'GetAllRoles', 'GetRoleById', 'CreateRole', 'UpdateRole', 'DeleteRole', 'CreateUser', 'GetAllUsers',
            'GetUserById', 'UpdateUser', 'DeleteUser', 'GetUserDataById', 'UpdateUserDataById', 'GetUserAuthorityById',
            'ResetPasswordByEmail', 'CreateRole'];
        $access_type = [1, 3, 3, 3, 3, 3, 2, 2, 2, 2, 2, 4, 4, 4, 4, 4, 4, 4, 4, 4, 2];

        foreach ($actionKey as $k => $valor) {
            DB::table('application_types')->insert([
                'action_name' => $action_name[$k],
                'access_type' =>$access_type[$k],
                'actionKey' => $valor,
            ]);
        }
    }
}
