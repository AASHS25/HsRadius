<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Tenancy\CurrentTenant::class);
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Super-admin (landlord) bypasses every gate.
        Gate::before(fn (User $user) => $user->isSuperAdmin() ? true : null);

        Gate::define('manage-tenants', fn (User $user) => false); // super-admin only (via before)
        Gate::define('manage-packages', fn (User $user) => $user->isAdmin());
        Gate::define('manage-nas', fn (User $user) => $user->isAdmin());
        Gate::define('manage-billing', fn (User $user) => $user->isAdmin());
        Gate::define('manage-customers', fn (User $user) => $user->isAdmin() || $user->isOperator());
        Gate::define('manage-vouchers', fn (User $user) => $user->isAdmin() || $user->isOperator());
        Gate::define('manage-sessions', fn (User $user) => $user->isAdmin() || $user->isOperator());
        Gate::define('view-reports', fn (User $user) => $user->isAdmin() || $user->isOperator());
        Gate::define('manage-settings', fn (User $user) => $user->isAdmin());
    }
}
