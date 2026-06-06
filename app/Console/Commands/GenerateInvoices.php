<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    protected $signature = 'radius:generate-invoices';
    protected $description = 'Generate monthly invoices for active customers';

    public function handle(WhatsAppService $wa): int
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
                'tenant_id' => $customer->tenant_id,
                'invoice_number' => Invoice::generateNumber(),
                'customer_id' => $customer->id,
                'package_id' => $customer->package_id,
                'amount' => $customer->package->price,
                'status' => 'unpaid',
                'due_date' => $customer->expiry_date->toDateString(),
            ]);

            $wa->send($customer->tenant_id, $customer->phone,
                "Halo {$customer->fullname}, tagihan baru sebesar Rp ".number_format($customer->package->price, 0, ',', '.')
                ." telah dibuat. Jatuh tempo {$customer->expiry_date->format('d/m/Y')}.");

            $count++;
        }

        $this->info("Generated {$count} invoices.");
        return Command::SUCCESS;
    }
}
