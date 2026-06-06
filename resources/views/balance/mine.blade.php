@extends('layouts.app')

@section('title', 'Saldo')

@section('content')
<div class="page-header">
    <h3>Saldo Saya</h3>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item active">Saldo</li></ol></nav>
</div>

<div class="card mb-4"><div class="card-body">
    <div class="text-muted small">Saldo saat ini</div>
    <div class="fw-bold" style="font-size: 1.8rem; color: #1e293b;">Rp {{ number_format($tenant->balance, 0, ',', '.') }}</div>
    <div class="small text-muted">Untuk top-up saldo, hubungi admin/landlord.</div>
</div></div>

<div class="card"><div class="card-header">Riwayat Transaksi</div><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead><tr><th>Waktu</th><th>Tipe</th><th>Keterangan</th><th class="text-end">Jumlah</th><th class="text-end">Saldo</th></tr></thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td class="small">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $tx->type }}</td>
                    <td>{{ $tx->description ?? '-' }}</td>
                    <td class="text-end {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div></div>
@endsection
