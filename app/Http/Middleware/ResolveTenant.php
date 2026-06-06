<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active tenant from the authenticated user.
 *
 * Single domain, many users: the tenant is derived from users.tenant_id.
 * Super-admins (tenant_id = null) get no tenant context, so the global
 * TenantScope adds no filter and they can see across all tenants.
 */
class ResolveTenant
{
    public function __construct(protected CurrentTenant $currentTenant)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            $this->currentTenant->set($user->tenant);
        }

        return $next($request);
    }
}
