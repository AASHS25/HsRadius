@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<div class="page-header">
    <h3>Pengaturan</h3>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Pengaturan</li></ol></nav>
</div>

@unless($tenantId)
    <div class="alert alert-warning">Anda login sebagai super-admin (tanpa tenant). Pengaturan payment &amp; WhatsApp bersifat per-tenant — login sebagai admin tenant untuk mengubahnya.</div>
@endunless

<form method="POST" action="{{ route('settings.update') }}">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-qr-code me-1"></i> Payment Gateway (Tripay — QRIS)</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Mode</label>
                    <select name="payment_tripay_mode" class="form-select">
                        <option value="sandbox" {{ ($val['payment.tripay_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ ($val['payment.tripay_mode'] ?? '') === 'production' ? 'selected' : '' }}>Production</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Merchant Code</label>
                    <input name="payment_tripay_merchant_code" class="form-control" value="{{ $val['payment.tripay_merchant_code'] ?? '' }}">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">API Key</label>
                    <input name="payment_tripay_api_key" class="form-control" value="{{ $val['payment.tripay_api_key'] ?? '' }}">
                </div>
                <div class="col-md-12 mb-2">
                    <label class="form-label">Private Key</label>
                    <input name="payment_tripay_private_key" class="form-control" value="{{ $val['payment.tripay_private_key'] ?? '' }}">
                </div>
            </div>
            <div class="small text-muted">Callback URL (paste di dashboard Tripay): <code>{{ url('/webhook/payment/tripay') }}</code></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-whatsapp me-1"></i> WhatsApp Gateway</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Provider</label>
                    <select name="wa_provider" class="form-select">
                        <option value="fonnte" {{ ($val['wa.provider'] ?? 'fonnte') === 'fonnte' ? 'selected' : '' }}>Fonnte</option>
                        <option value="wablas" {{ ($val['wa.provider'] ?? '') === 'wablas' ? 'selected' : '' }}>Wablas</option>
                    </select>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">API Key / Token</label>
                    <input name="wa_api_key" class="form-control" value="{{ $val['wa.api_key'] ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sender / Device ID <span class="text-muted small">(Wablas)</span></label>
                    <input name="wa_sender" class="form-control" value="{{ $val['wa.sender'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary">Simpan Pengaturan</button>
</form>

<div class="card mt-4">
    <div class="card-header">Tes Kirim WhatsApp</div>
    <div class="card-body">
        <form method="POST" action="{{ route('settings.test-wa') }}" class="row g-2 align-items-end" style="max-width: 520px;">
            @csrf
            <div class="col-8">
                <label class="form-label small">Nomor tujuan</label>
                <input name="test_phone" class="form-control" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="col-4">
                <button class="btn btn-success w-100"><i class="bi bi-send me-1"></i>Tes Kirim</button>
            </div>
        </form>
        <div class="small text-muted mt-2">Simpan pengaturan WhatsApp dulu sebelum tes.</div>
    </div>
</div>
@endsection
