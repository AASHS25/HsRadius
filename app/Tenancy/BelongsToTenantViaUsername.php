<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;

/**
 * Tenant scope for RADIUS tables written by FreeRADIUS (radacct, radpostauth).
 *
 * These tables have no tenant_id column (FreeRADIUS writes them directly), so
 * reads are scoped by username belonging to the current tenant's customers.
 * Customer usernames are globally unique, so this is unambiguous. When no
 * tenant is in context (CLI / super-admin), no filter is applied.
 */
trait BelongsToTenantViaUsername
{
    public static function bootBelongsToTenantViaUsername(): void
    {
        static::addGlobalScope('tenant_via_username', function (Builder $builder) {
            $tenantId = app(CurrentTenant::class)->id();

            if ($tenantId !== null) {
                $table = $builder->getModel()->getTable();
                $builder->whereIn($table.'.username', function ($query) use ($tenantId) {
                    $query->select('username')->from('customers')->where('tenant_id', $tenantId);
                });
            }
        });
    }
}
