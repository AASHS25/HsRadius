<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Console\Command;

class CheckExpiredCustomers extends Command
{
    protected $signature = 'radius:check-expired';
    protected $description = 'Check and suspend (isolir) expired customers';

    public function handle(RadiusService $radiusService, MikrotikService $mikrotikService): int
    {
        $expired = Customer::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with('nas')
            ->get();

        $count = 0;
        foreach ($expired as $customer) {
            $customer->update(['status' => 'expired']);
            $radiusService->suspendCustomer($customer->username);

            // Kick the live session on the router (safely no-ops if unreachable).
            if ($customer->nas) {
                $mikrotikService->disconnectUser($customer->nas, $customer->username, $customer->service_type);
            }

            $count++;
        }

        $this->info("Processed {$count} expired customers (isolir).");
        return Command::SUCCESS;
    }
}
