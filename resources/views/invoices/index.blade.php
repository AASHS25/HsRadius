@extends('layouts.app')

@section('title', 'Invoice')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Invoice</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Invoice</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Buat Invoice
    </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="No Invoice / Pelanggan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices ?? [] as $invoice)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $invoice->invoice_number }}</span>
                            </td>
                            <td>
                                {{ $invoice->customer->fullname ?? '-' }}
                                <br>
                                <small class="text-muted">{{ $invoice->customer->username ?? '' }}</small>
                            </td>
                            <td>{{ $invoice->package->name ?? '-' }}</td>
                            <td>
                                <span class="fw-semibold">Rp {{ number_format($invoice->amount ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @switch($invoice->status)
                                    @case('paid')
                                        <span class="badge bg-success">Lunas</span>
                                        @break
                                    @case('unpaid')
                                        <span class="badge bg-warning text-dark">Belum Bayar</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-info">Sebagian</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-secondary">Dibatalkan</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($invoice->due_date)
                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                                    @if($invoice->status == 'unpaid' && \Carbon\Carbon::parse($invoice->due_date)->isPast())
                                        <br><span class="badge bg-danger-subtle text-danger">Jatuh Tempo</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-outline-secondary" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($invoice->status == 'unpaid')
                                        <form method="POST" action="{{ route('invoices.pay', $invoice->id) }}" class="d-inline" onsubmit="return confirm('Tandai invoice ini sebagai lunas?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="Bayar">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus invoice ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-receipt" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">Belum ada invoice</p>
                                <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-lg me-1"></i> Buat Invoice
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($invoices) && $invoices->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted">
                    Menampilkan {{ $invoices->firstItem() }} - {{ $invoices->lastItem() }} dari {{ $invoices->total() }} invoice
                </small>
                {{ $invoices->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
