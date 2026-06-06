@extends('layouts.app')

@section('title', 'Saldo Tenant')

@section('content')
<div class="page-header">
    <h3>Saldo Tenant</h3>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item">Landlord</li><li class="breadcrumb-item active">Saldo</li></ol></nav>
</div>

<div class="card mb-4">
    <div class="card-header">Top-up / Penyesuaian Saldo</div>
    <div class="card-body">
        <form method="POST" action="{{ route('balance.topup') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Tenant</label>
                <select name="tenant_id" class="form-select" required>
                    <option value="">— Pilih tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} (Saldo: Rp {{ number_format($t->balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipe</label>
                <select name="type" class="form-select">
                    <option value="topup">Top-up</option>
                    <option value="deduct">Potong</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jumlah (Rp)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Keterangan</label>
                <input name="description" class="form-control">
            </div>
            <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5"><div class="card"><div class="card-header">Saldo per Tenant</div><div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Tenant</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
                @foreach($tenants as $t)
                    <tr><td>{{ $t->name }}</td><td class="text-end fw-bold">Rp {{ number_format($t->balance, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div></div></div>
    <div class="col-md-7"><div class="card"><div class="card-header">Transaksi Terbaru</div><div class="card-body p-0"><div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Waktu</th><th>Tenant</th><th>Tipe</th><th class="text-end">Jumlah</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td class="small">{{ $tx->created_at->format('d/m H:i') }}</td>
                        <td>{{ $tx->tenant->name ?? '-' }}</td>
                        <td><span class="badge {{ $tx->amount >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $tx->type }}</span></td>
                        <td class="text-end">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div></div></div>
</div>
@endsection
