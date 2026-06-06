<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that restricts queries to the current tenant.
 *
 * If no tenant is resolved (CLI/seeder/super-admin), no constraint is added,
 * keeping cross-tenant/global access intact.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(CurrentTenant::class)->id();

        if ($tenantId !== null) {
            $builder->where($model->getTable().'.tenant_id', $tenantId);
        }
    }
}
