<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\RadiusService;
use Illuminate\Console\Command;

class CheckExpiredCustomers extends Command
{
    protected $signature = 'radius:check-expired';
    protected $description = 'Check and suspend expired customers';

    public function handle(RadiusService $radiusService): int
    {
        $expired = Customer::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $customer) {
            $customer->update(['status' => 'expired']);
            $radiusService->suspendCustomer($customer->username);
            $count++;
        }

        $this->info("Processed {$count} expired customers.");
        return Command::SUCCESS;
    }
}
