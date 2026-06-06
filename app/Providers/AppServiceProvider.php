<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Tenancy\CurrentTenant::class);
    }

    public function boot(): void
    {
        //
    }
}
