<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use Illuminate\Console\Command;

class CheckTenantBilling extends Command
{
    protected $signature = 'tenants:check-billing';

    protected $description = 'Suspend tenants that have overdue unpaid subscription invoices';

    public function handle(): int
    {
        $overdueTenantIds = TenantInvoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', now())
            ->pluck('tenant_id')
            ->unique();

        $count = 0;

        Tenant::whereIn('id', $overdueTenantIds)
            ->where('status', '!=', 'suspended')
            ->get()
            ->each(function (Tenant $tenant) use (&$count) {
                $tenant->update(['status' => 'suspended']);
                $count++;
                $this->warn("Suspended (overdue): {$tenant->name}");
            });

        $this->info("Tenant billing check complete. {$count} tenant(s) suspended.");

        return self::SUCCESS;
    }
}
