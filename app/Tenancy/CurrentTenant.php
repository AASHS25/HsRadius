<?php

namespace App\Tenancy;

use App\Models\Tenant;

/**
 * Holds the tenant resolved for the current request/CLI context.
 *
 * When no tenant is set (e.g. CLI, seeders, central login, super-admin),
 * the TenantScope applies no filter, so global access still works.
 */
class CurrentTenant
{
    protected ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    public function check(): bool
    {
        return $this->tenant !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
