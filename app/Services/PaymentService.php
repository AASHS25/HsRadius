<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tripay (QRIS) payment integration. Credentials are stored per tenant in
 * the settings table, so each operator collects payments to their own
 * Tripay merchant account. Without credentials, online payment is disabled
 * and the app falls back to manual "mark as paid".
 */
class PaymentService
{
    public function isConfigured(?int $tenantId): bool
    {
        return (bool) Setting::get($tenantId, 'payment.tripay_api_key')
            && (bool) Setting::get($tenantId, 'payment.tripay_private_key')
            && (bool) Setting::get($tenantId, 'payment.tripay_merchant_code');
    }

    protected function baseUrl(?int $tenantId): string
    {
        return Setting::get($tenantId, 'payment.tripay_mode', 'sandbox') === 'production'
            ? 'https://tripay.co.id/api'
            : 'https://tripay.co.id/api-sandbox';
    }

    /**
     * Create a Tripay QRIS transaction for an invoice and return the
     * hosted checkout URL, or null if not configured / on error.
     */
    public function createCheckoutUrl(Invoice $invoice): ?string
    {
        $tenantId = $invoice->tenant_id;

        if (! $this->isConfigured($tenantId)) {
            return null;
        }

        $apiKey = Setting::get($tenantId, 'payment.tripay_api_key');
        $privateKey = Setting::get($tenantId, 'payment.tripay_private_key');
        $merchant = Setting::get($tenantId, 'payment.tripay_merchant_code');

        $invoice->loadMissing('customer');
        $ref = $invoice->invoice_number;
        $amount = (int) round((float) $invoice->amount);
        $signature = hash_hmac('sha256', $merchant.$ref.$amount, (string) $privateKey);

        try {
            $response = Http::withToken($apiKey)->asForm()->post($this->baseUrl($tenantId).'/transaction/create', [
                'method' => 'QRIS',
                'merchant_ref' => $ref,
                'amount' => $amount,
                'customer_name' => $invoice->customer->fullname ?? 'Pelanggan',
                'customer_email' => $invoice->customer->email ?: 'noreply@hsradius.local',
                'order_items' => [
                    ['name' => 'Tagihan '.$ref, 'price' => $amount, 'quantity' => 1],
                ],
                'callback_url' => route('webhook.payment.tripay'),
                'return_url' => route('portal.invoices'),
                'signature' => $signature,
            ]);

            return data_get($response->json(), 'data.checkout_url');
        } catch (\Throwable $e) {
            Log::error('Tripay transaction create failed: '.$e->getMessage());

            return null;
        }
    }
}
