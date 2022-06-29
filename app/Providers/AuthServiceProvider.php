<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        Passport::enableImplicitGrant();

        Passport::tokensCan([
            'orderCreate' => 'can create orders',
            'orderEdit' => 'can edit orders',
            'orderDelete' => 'can delete orders',
            'orderGetStatus' => 'can see order status',
            'waveCreate' => 'can create waves',
            'waveEdit' => 'can edit waves',
            'waveDelete' => 'can delete waves',
            'waveGetStatus' => 'can see wave status',
        ]);        
    }
}
