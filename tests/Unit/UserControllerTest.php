<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class UserControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * @var $user
     * @test
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     * No autorizado
     */
    public function testAuthorization()
    {

        $this->json('GET', 'api/v1/users/all', ['Accept' => 'application/json'])
            ->assertStatus(401)
            ->assertJson([
                "message" => true
            ]);
    }

    /**
     * @test
     * Obtener todos los usuarios
     */
    public function testGetAllUsers()
    {
        $this->withoutMiddleWare();

        $this->json('GET', 'api/v1/users/all', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * testeo login
     */
    public function testLogin()
    {
        $this->withoutMiddleWare();
        $user = Auth::loginUsingId(1);
        $request = [
            "username" => $user->email,
            "password" => "secret",
        ];

        $this->json('POST', 'api/v1/login', $request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * Show usuario
     */
    public function testShowUser()
    {
        $this->withoutMiddleWare();
        $this->user = Auth::loginUsingId(1);

        $this->json('GET', 'api/v1/user/showlogued/', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test getAuthority
     */
    public function testGetAuthority()
    {
        $this->withoutMiddleWare();
        $this->user = Auth::loginUsingId(1);

        $this->json('GET', 'api/v1/oauth/me/hasAuthority', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * get accessByUserId
     */
    public function testAccessByUserId()
    {
        $this->withoutMiddleWare();
        $user = Auth::loginUsingId(1);

        $this->json('GET', 'api/v1/oauth/me/'.$user->id.'/full', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test getAuthorities
     */
    public function testGetAuthorities()
    {
        $this->withoutMiddleWare();
        $this->user = Auth::loginUsingId(1);

        $this->json('GET', 'api/v1/user/'.$this->user->id.'/authorities', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test checkPermission
     */
    public function testCheckPermission()
    {
        $this->withoutMiddleWare();

        $this->user = Auth::loginUsingId(1);
        $request = [
            "match" => true
        ];

        $this->json('GET', 'api/v1/user/permission', $request, ['Accept' => 'application/json'])
            ->assertStatus(200);
    }

    /**
     * @test
     * test getPermissions
     */
    public function testGetPermissions()
    {
        $this->withoutMiddleWare();
        $this->user = Auth::loginUsingId(1);

        $this->json('GET', 'api/v1/user/getpermissions', ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test acceder user edit
     */
    public function testEditUser()
    {
        $this->withoutMiddleWare();
        $this->user = Auth::loginUsingId(1);
        $id = $this->user->id;

        $this->json('GET', 'api/v1/user/edit/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test crea user
     */
    public function testCreateUser()
    {
        $this->withoutMiddleWare();
        $request = $this->user;

        $this->json('POST', 'api/v1/user/create',$request->toArray(), ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test update user
     */
    public function testUpdateUser()
    {
        $this->withoutMiddleWare();
        $user = User::find(1);
        $user->name = "Name Updated";

        $this->json('POST', 'api/v1/user/update',$user->toArray(), ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test delete user
     */
    public function testDeleteUser()
    {
        $this->withoutMiddleWare();
        $user = User::find(1);

        $this->json('GET', 'api/v1/user/delete/'.$user->id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }


    /**
     * @test
     * test change password user
     */
    public function testChangePasswordUser()
    {
        $this->withoutMiddleWare();

        $this->user = Auth::loginUsingId(19);


        $request = [
            "old_password"              => "staging1",
            "new_password"              => "123456",
            "new_password_confirmation" => "123456",
        ];

        $this->json('POST', 'api/v1/user/changepassword',$request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test change password admin
     */
    public function testChangePasswordByAdmin()
    {
        $this->withoutMiddleWare();

        $user = Auth::loginUsingId(1);

        $request = [
            "email"                     => $user->email,
            "new_password"              => $user->password,
            "new_password_confirmation" => $user->password
        ];

        $this->json('POST', 'api/v1/user/changepasswordadmin',$request, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * @test
     * test getUsersRol
     */
    public function testGetUsersRol()
    {
        $this->withoutMiddleWare();

        $user = Auth::loginUsingId(1);

        $id = $user->id;

        $this->json('GET', 'api/v1/users/roleusers/'.$id, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([]);
    }

}

