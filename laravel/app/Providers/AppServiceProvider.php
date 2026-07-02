<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;


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
    // If a user is logged in, use their shop_id. If it's empty (Super Admin), fall back to 0.
    if (auth()->check()) {
        $teamId = auth()->user()->shop_id ?? 0;
        app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
    }
}
}
