<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends WhatsApp notifications via a per-tenant gateway (Fonnte or Wablas).
 * Returns false silently when not configured, so callers can fire-and-forget
 * without breaking the main flow.
 */
class WhatsAppService
{
    public function isConfigured(?int $tenantId): bool
    {
        return (bool) Setting::get($tenantId, 'wa.api_key');
    }

    public function send(?int $tenantId, ?string $phone, string $message): bool
    {
        if (! $phone || ! $this->isConfigured($tenantId)) {
            return false;
        }

        $provider = Setting::get($tenantId, 'wa.provider', 'fonnte');
        $apiKey = (string) Setting::get($tenantId, 'wa.api_key');
        $target = $this->normalize($phone);

        try {
            if ($provider === 'wablas') {
                $response = Http::withHeaders(['Authorization' => $apiKey])->asForm()->post(
                    'https://wablas.com/api/send-message',
                    ['phone' => $target, 'message' => $message],
                );
            } else { // fonnte
                $response = Http::withHeaders(['Authorization' => $apiKey])->asForm()->post(
                    'https://api.fonnte.com/send',
                    ['target' => $target, 'message' => $message],
                );
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed: '.$e->getMessage());

            return false;
        }
    }

    /** Normalize a local Indonesian number to 62 international format. */
    protected function normalize(string $phone): string
    {
        $p = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($p, '0')) {
            $p = '62'.substr($p, 1);
        }

        return $p;
    }
}
