@extends('layouts.app')

@section('title', 'Tagihan Tenant')

@section('content')
<div class="page-header">
    <h3>Tagihan Tenant</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Landlord</li>
            <li class="breadcrumb-item active">Tagihan Tenant</li>
        </ol>
    </nav>
</div>

<div class="card mb-4">
    <div class="card-header">Buat Tagihan Langganan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('subscriptions.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Tenant</label>
                <select name="tenant_id" class="form-select" required>
                    <option value="">— Pilih tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} @if($t->plan)({{ $t->plan->name }})@else(tanpa paket)@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mulai Periode</label>
                <input type="date" name="period_start" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jatuh Tempo</label>
                <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->toDateString() }}" required>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Buat</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Tenant</th><th>Paket</th><th>Jumlah</th>
                        <th>Periode</th><th>Jatuh Tempo</th><th>Status</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ $inv->id }}</td>
                            <td class="fw-semibold">{{ $inv->tenant->name ?? '-' }}</td>
                            <td>{{ $inv->plan->name ?? '-' }}</td>
                            <td>Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                            <td>{{ $inv->period_start->format('d/m/Y') }} &ndash; {{ $inv->period_end->format('d/m/Y') }}</td>
                            <td>{{ $inv->due_date->format('d/m/Y') }}</td>
                            <td>
                                @if($inv->status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($inv->isOverdue())
                                    <span class="badge bg-danger">Nunggak</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum bayar</span>
                                @endif
                            </td>
                            <td>
                                @if($inv->status !== 'paid')
                                    <form method="POST" action="{{ route('subscriptions.pay', $inv) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg me-1"></i>Lunas</button>
                                    </form>
                                @else
                                    <span class="text-muted small">{{ $inv->paid_at?->format('d/m/Y') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
