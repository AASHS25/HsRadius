<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    protected $signature = 'radius:generate-invoices';
    protected $description = 'Generate monthly invoices for active customers';

    public function handle(): int
    {
        $customers = Customer::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(7)])
            ->with('package')
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            $existingInvoice = Invoice::where('customer_id', $customer->id)
                ->where('status', 'unpaid')
                ->where('due_date', '>=', now())
                ->exists();

            if ($existingInvoice) {
                continue;
            }

            Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id' => $customer->package_id,
                'amount' => $customer->package->price,
                'status' => 'unpaid',
                'due_date' => $customer->expiry_date->toDateString(),
            ]);
            $count++;
        }

        $this->info("Generated {$count} invoices.");
        return Command::SUCCESS;
    }
}
