<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('is-manager-or-agent', function (User $user) {
            return $user->role === 'manager' or $user->role === 'agent';
        });

        Gate::define('is-helper', function (User $user) {
            return $user->role === 'helper';
        });

        Gate::define('is-help-seeker', function (User $user) {
            return $user->role === 'help_seeker';
        });
    }
}
