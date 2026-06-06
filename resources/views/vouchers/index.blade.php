@extends('layouts.app')

@section('title', 'Manajemen Voucher')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Manajemen Voucher</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Voucher</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('vouchers.generate') }}" class="btn btn-primary">
        <i class="bi bi-lightning me-1"></i> Generate Voucher
    </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('vouchers.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="unused" {{ request('status') == 'unused' ? 'selected' : '' }}>Belum Dipakai</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Sudah Dipakai</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Paket</label>
                    <select name="package_id" class="form-select">
                        <option value="">Semua Paket</option>
                        @foreach($packages ?? [] as $pkg)
                            <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Batch</label>
                    <select name="batch_name" class="form-select">
                        <option value="">Semua Batch</option>
                        @foreach($batches ?? [] as $batch)
                            <option value="{{ $batch }}" {{ request('batch_name') == $batch ? 'selected' : '' }}>{{ $batch }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table with Bulk Actions --}}
<form id="bulkForm" method="POST">
    @csrf
    <div class="card">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="form-check me-3">
                    <input type="checkbox" id="selectAll" class="form-check-input">
                    <label for="selectAll" class="form-check-label small fw-semibold">Pilih Semua</label>
                </div>
                <span class="text-muted small" id="selectedCount"></span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <select name="template" class="form-select form-select-sm" style="width:auto;" title="Template cetak">
                    <option value="default">Grid 4</option>
                    <option value="card">Kartu</option>
                    <option value="thermal">Thermal</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrintSelected">
                    <i class="bi bi-printer me-1"></i> Print Terpilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeleteSelected">
                    <i class="bi bi-trash me-1"></i> Hapus Terpilih
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Kode Voucher</th>
                            <th>Paket</th>
                            <th>Status</th>
                            <th>Batch</th>
                            <th>Digunakan Oleh</th>
                            <th>Tanggal Pakai</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vouchers ?? [] as $voucher)
                            <tr>
                                <td>
                                    <input type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}" class="form-check-input voucher-check">
                                </td>
                                <td>
                                    <code style="font-size: 0.95rem; letter-spacing: 1px;">{{ $voucher->code }}</code>
                                </td>
                                <td>{{ $voucher->package->name ?? '-' }}</td>
                                <td>
                                    @switch($voucher->status)
                                        @case('unused')
                                            <span class="badge bg-success">Tersedia</span>
                                            @break
                                        @case('used')
                                            <span class="badge bg-secondary">Terpakai</span>
                                            @break
                                        @case('expired')
                                            <span class="badge bg-danger">Expired</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $voucher->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $voucher->batch_name ?? '-' }}</span>
                                </td>
                                <td>{{ $voucher->used_by ?? '-' }}</td>
                                <td>
                                    @if($voucher->used_at)
                                        {{ \Carbon\Carbon::parse($voucher->used_at)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('vouchers.destroy', $voucher->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus voucher ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="bi bi-ticket-perforated" style="font-size: 3rem;"></i>
                                    <p class="mb-0 mt-2">Belum ada voucher</p>
                                    <a href="{{ route('vouchers.generate') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-lightning me-1"></i> Generate Voucher
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($vouchers) && $vouchers->hasPages())
            <div class="card-footer bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted">
                        Menampilkan {{ $vouchers->firstItem() }} - {{ $vouchers->lastItem() }} dari {{ $vouchers->total() }} voucher
                    </small>
                    {{ $vouchers->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Select all checkbox
    var selectAll = document.getElementById('selectAll');
    var voucherChecks = document.querySelectorAll('.voucher-check');

    function updateSelectedCount() {
        var count = document.querySelectorAll('.voucher-check:checked').length;
        var countEl = document.getElementById('selectedCount');
        countEl.textContent = count > 0 ? count + ' voucher dipilih' : '';
    }

    selectAll.addEventListener('change', function () {
        voucherChecks.forEach(function (cb) { cb.checked = selectAll.checked; });
        updateSelectedCount();
    });

    voucherChecks.forEach(function (cb) {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Print selected
    document.getElementById('btnPrintSelected').addEventListener('click', function () {
        var checked = document.querySelectorAll('.voucher-check:checked');
        if (checked.length === 0) {
            alert('Pilih minimal satu voucher untuk dicetak.');
            return;
        }
        var form = document.getElementById('bulkForm');
        form.action = '{{ route("vouchers.print") }}';
        form.target = '_blank';
        form.submit();
        form.target = '';
    });

    // Delete selected
    document.getElementById('btnDeleteSelected').addEventListener('click', function () {
        var checked = document.querySelectorAll('.voucher-check:checked');
        if (checked.length === 0) {
            alert('Pilih minimal satu voucher untuk dihapus.');
            return;
        }
        if (confirm('Yakin ingin menghapus ' + checked.length + ' voucher yang dipilih?')) {
            var form = document.getElementById('bulkForm');
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            form.action = '{{ route("vouchers.bulk-delete") }}';
            form.target = '';
            form.submit();
        }
    });
</script>
@endpush
