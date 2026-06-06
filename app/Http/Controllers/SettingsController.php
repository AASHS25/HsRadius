<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /** Setting keys managed on this page. */
    protected array $keys = [
        'payment.tripay_mode',
        'payment.tripay_merchant_code',
        'payment.tripay_api_key',
        'payment.tripay_private_key',
        'wa.provider',
        'wa.api_key',
        'wa.sender',
    ];

    public function edit()
    {
        $tenantId = Auth::user()->tenant_id;

        $val = [];
        foreach ($this->keys as $key) {
            $val[$key] = Setting::get($tenantId, $key, '');
        }

        return view('settings.index', compact('tenantId', 'val'));
    }

    public function update(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Super-admin tidak punya tenant. Atur pengaturan lewat akun admin tenant.');
        }

        foreach ($this->keys as $key) {
            $field = str_replace('.', '_', $key);
            Setting::put($tenantId, $key, $request->input($field));
        }

        return redirect()->route('settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function testWa(Request $request)
    {
        $data = $request->validate(['test_phone' => 'required|string']);
        $tenantId = Auth::user()->tenant_id;

        $ok = app(WhatsAppService::class)->send(
            $tenantId,
            $data['test_phone'],
            'Tes notifikasi WhatsApp dari HsRadius. Jika Anda menerima pesan ini, konfigurasi sudah benar.'
        );

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Pesan tes terkirim.' : 'Gagal mengirim. Pastikan API key, provider, dan nomor sudah benar.'
        );
    }
}
