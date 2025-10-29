<?php

namespace App\Providers;

use App\Listeners\ValidateUserRoleOnLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Validar el rol del usuario al iniciar sesión
        Event::listen(
            Login::class,
            ValidateUserRoleOnLogin::class,
        );
    }
}
