@extends('layouts.app')

@section('title', isset($invoice) ? 'Edit Invoice' : 'Buat Invoice')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>{{ isset($invoice) ? 'Edit Invoice' : 'Buat Invoice' }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoice</a></li>
                <li class="breadcrumb-item active">{{ isset($invoice) ? 'Edit' : 'Buat' }}</li>
            </ol>
        </nav>
    </div>
</div>

<form method="POST" action="{{ isset($invoice) ? route('invoices.update', $invoice->id) : route('invoices.store') }}">
    @csrf
    @if(isset($invoice))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-receipt me-2"></i>Detail Invoice
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_id" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers ?? [] as $customer)
                                    <option value="{{ $customer->id }}"
                                        data-package="{{ $customer->package_id }}"
                                        data-price="{{ $customer->package->price ?? 0 }}"
                                        {{ old('customer_id', $invoice->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->fullname ?? $customer->username }} ({{ $customer->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="package_id" class="form-label">Paket</label>
                            <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id">
                                <option value="">-- Pilih Paket --</option>
                                @foreach($packages ?? [] as $pkg)
                                    <option value="{{ $pkg->id }}"
                                        data-price="{{ $pkg->price }}"
                                        {{ old('package_id', $invoice->package_id ?? '') == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }} - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $invoice->amount ?? '') }}" required min="0">
                            </div>
                            @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', isset($invoice) ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : now()->addDays(7)->format('Y-m-d')) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="notes" class="form-label">Catatan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk invoice ini...">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> {{ isset($invoice) ? 'Perbarui' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            {{-- Payment Info for Edit --}}
            @if(isset($invoice))
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>Info Invoice
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">No. Invoice</td>
                                <td class="fw-semibold text-end">{{ $invoice->invoice_number }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td class="text-end">
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
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Dibuat</td>
                                <td class="text-end">{{ $invoice->created_at ? $invoice->created_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($invoice->status == 'paid')
                    <div class="card border-success">
                        <div class="card-body text-center" style="background: #f0fdf4;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 2.5rem;"></i>
                            <h5 class="text-success mt-2 mb-1">LUNAS</h5>
                            @if($invoice->paid_date)
                                <p class="text-muted mb-1 small">Dibayar: {{ \Carbon\Carbon::parse($invoice->paid_date)->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($invoice->payment_method)
                                <p class="text-muted mb-0 small">Metode: {{ $invoice->payment_method }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightbulb me-2"></i>Tips
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0 ps-3 text-muted">
                            <li class="mb-1">Pilih pelanggan untuk otomatis mengisi paket dan harga.</li>
                            <li class="mb-1">Anda dapat mengubah jumlah secara manual sesuai kebutuhan.</li>
                            <li class="mb-1">Invoice akan otomatis mendapat nomor unik.</li>
                            <li>Jatuh tempo default 7 hari dari sekarang.</li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Auto-fill package and amount when customer is selected
    var customerSelect = document.getElementById('customer_id');
    var packageSelect = document.getElementById('package_id');
    var amountInput = document.getElementById('amount');

    customerSelect.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        var pkgId = opt.dataset.package;
        var price = opt.dataset.price;

        if (pkgId) {
            packageSelect.value = pkgId;
        }
        if (price && parseInt(price) > 0) {
            amountInput.value = price;
        }
    });

    packageSelect.addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        var price = opt.dataset.price;

        if (price && parseInt(price) > 0) {
            amountInput.value = price;
        }
    });
</script>
@endpush
