@extends('layouts.app')

@section('title', 'Generate Voucher')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Generate Voucher</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Voucher</a></li>
                <li class="breadcrumb-item active">Generate</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <form method="POST" action="{{ route('vouchers.store') }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Konfigurasi Voucher
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="package_id" class="form-label">Paket Layanan <span class="text-danger">*</span></label>
                            <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($packages ?? [] as $pkg)
                                    <option value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'selected' : '' }}>
                                        {{ $pkg->name }} - Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Jumlah Voucher <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 10) }}" min="1" max="1000" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="code_length" class="form-label">Panjang Kode</label>
                            <input type="number" class="form-control @error('code_length') is-invalid @enderror" id="code_length" name="code_length" value="{{ old('code_length', 8) }}" min="4" max="16">
                            @error('code_length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prefix" class="form-label">Prefix (opsional)</label>
                            <input type="text" class="form-control @error('prefix') is-invalid @enderror" id="prefix" name="prefix" value="{{ old('prefix') }}" placeholder="Contoh: HSR-" maxlength="8">
                            @error('prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="batch_name" class="form-label">Nama Batch</label>
                            <input type="text" class="form-control @error('batch_name') is-invalid @enderror" id="batch_name" name="batch_name" value="{{ old('batch_name', 'BATCH-' . date('Ymd-His')) }}">
                            @error('batch_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="validity_value" class="form-label">Masa Aktif Voucher</label>
                            <input type="number" class="form-control @error('validity_value') is-invalid @enderror" id="validity_value" name="validity_value" value="{{ old('validity_value') }}" placeholder="Kosongkan untuk mengikuti paket" min="1">
                            @error('validity_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validity_unit" class="form-label">Satuan Waktu</label>
                            <select class="form-select @error('validity_unit') is-invalid @enderror" id="validity_unit" name="validity_unit">
                                <option value="">Dari paket</option>
                                <option value="minutes" {{ old('validity_unit') == 'minutes' ? 'selected' : '' }}>Menit</option>
                                <option value="hours" {{ old('validity_unit') == 'hours' ? 'selected' : '' }}>Jam</option>
                                <option value="days" {{ old('validity_unit') == 'days' ? 'selected' : '' }}>Hari</option>
                                <option value="months" {{ old('validity_unit') == 'months' ? 'selected' : '' }}>Bulan</option>
                            </select>
                            @error('validity_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                    <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-lightning me-1"></i> Generate Voucher
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4 mb-3">
        {{-- Info Card --}}
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Informasi
            </div>
            <div class="card-body">
                <ul class="small mb-0 ps-3">
                    <li class="mb-1">Kode voucher dihasilkan secara acak</li>
                    <li class="mb-1">Karakter yang digunakan: A-Z (tanpa I, O) dan 2-9</li>
                    <li class="mb-1">Maksimum 1000 voucher per batch</li>
                    <li class="mb-1">Prefix akan ditambahkan di depan kode</li>
                    <li>Masa aktif kosong = mengikuti pengaturan paket</li>
                </ul>
            </div>
        </div>

        {{-- Preview --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-eye me-2"></i>Preview Kode Voucher
            </div>
            <div class="card-body">
                <div id="previewCodes" class="d-flex flex-wrap gap-2">
                    {{-- Generated by JS --}}
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-3 w-100" id="btnRefreshPreview">
                    <i class="bi bi-arrow-clockwise me-1"></i> Generate Ulang Preview
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function generatePreviewCodes() {
        var chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        var length = parseInt(document.getElementById('code_length').value) || 8;
        var prefix = document.getElementById('prefix').value || '';
        var container = document.getElementById('previewCodes');
        container.innerHTML = '';

        for (var i = 0; i < 6; i++) {
            var code = '';
            for (var j = 0; j < length; j++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            var badge = document.createElement('span');
            badge.className = 'badge bg-light text-dark border';
            badge.style.fontFamily = 'monospace';
            badge.style.fontSize = '0.85rem';
            badge.style.letterSpacing = '1px';
            badge.textContent = prefix + code;
            container.appendChild(badge);
        }
    }

    generatePreviewCodes();

    document.getElementById('btnRefreshPreview').addEventListener('click', generatePreviewCodes);
    document.getElementById('code_length').addEventListener('change', generatePreviewCodes);
    document.getElementById('prefix').addEventListener('input', generatePreviewCodes);
</script>
@endpush
