<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to every model owned by a tenant (Phase 2+).
 *
 * - Adds the TenantScope global scope (auto-filter by current tenant).
 * - Auto-fills tenant_id on create from the current tenant context.
 *
 * NOT applied to the User model: login is central (by email), so users are
 * resolved without a tenant in context.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $tenantId = app(CurrentTenant::class)->id();

            if ($tenantId !== null && empty($model->tenant_id)) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
