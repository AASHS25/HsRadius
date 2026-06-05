@extends('layouts.app')

@section('title', isset($package) ? 'Edit Paket' : 'Tambah Paket')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>{{ isset($package) ? 'Edit Paket' : 'Tambah Paket' }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('packages.index') }}">Paket Layanan</a></li>
                <li class="breadcrumb-item active">{{ isset($package) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
    </div>
</div>

<form method="POST" action="{{ isset($package) ? route('packages.update', $package->id) : route('packages.store') }}">
    @csrf
    @if(isset($package))
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Paket
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $package->name ?? '') }}" required placeholder="Contoh: Paket Internet 10Mbps">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Tipe Layanan <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="hotspot" {{ old('type', $package->type ?? '') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                            <option value="pppoe" {{ old('type', $package->type ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $package->price ?? '') }}" required min="0" step="1000">
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="shared_users" class="form-label">Shared Users</label>
                        <input type="number" class="form-control @error('shared_users') is-invalid @enderror" id="shared_users" name="shared_users" value="{{ old('shared_users', $package->shared_users ?? 1) }}" min="1">
                        @error('shared_users')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jumlah user yang bisa login bersamaan dengan paket ini.</small>
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Deskripsi paket...">{{ old('description', $package->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-speedometer me-2"></i>Kecepatan & Bandwidth
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="rate_up" class="form-label">Upload Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('rate_up') is-invalid @enderror" id="rate_up" name="rate_up" value="{{ old('rate_up', $package->rate_up ?? '') }}" required min="0">
                                <span class="input-group-text">Kbps</span>
                            </div>
                            @error('rate_up')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="rate_down" class="form-label">Download Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('rate_down') is-invalid @enderror" id="rate_down" name="rate_down" value="{{ old('rate_down', $package->rate_down ?? '') }}" required min="0">
                                <span class="input-group-text">Kbps</span>
                            </div>
                            @error('rate_down')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Burst Settings (Collapsible) --}}
                    <div class="mb-3">
                        <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#burstSettings" role="button" aria-expanded="{{ old('burst_rate_down', $package->burst_rate_down ?? '') ? 'true' : 'false' }}">
                            <i class="bi bi-lightning me-1"></i> Pengaturan Burst
                        </a>
                    </div>

                    <div class="collapse {{ old('burst_rate_down', $package->burst_rate_down ?? '') ? 'show' : '' }}" id="burstSettings">
                        <div class="border rounded p-3 mb-3" style="background: #f8fafc;">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="burst_rate_up" class="form-label small">Burst Upload</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_rate_up" name="burst_rate_up" value="{{ old('burst_rate_up', $package->burst_rate_up ?? '') }}" min="0">
                                        <span class="input-group-text">Kbps</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="burst_rate_down" class="form-label small">Burst Download</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_rate_down" name="burst_rate_down" value="{{ old('burst_rate_down', $package->burst_rate_down ?? '') }}" min="0">
                                        <span class="input-group-text">Kbps</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="burst_threshold_up" class="form-label small">Burst Threshold Upload</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_threshold_up" name="burst_threshold_up" value="{{ old('burst_threshold_up', $package->burst_threshold_up ?? '') }}" min="0">
                                        <span class="input-group-text">Kbps</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="burst_threshold_down" class="form-label small">Burst Threshold Download</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_threshold_down" name="burst_threshold_down" value="{{ old('burst_threshold_down', $package->burst_threshold_down ?? '') }}" min="0">
                                        <span class="input-group-text">Kbps</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="burst_time_up" class="form-label small">Burst Time Upload</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_time_up" name="burst_time_up" value="{{ old('burst_time_up', $package->burst_time_up ?? '') }}" min="0">
                                        <span class="input-group-text">detik</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="burst_time_down" class="form-label small">Burst Time Download</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control" id="burst_time_down" name="burst_time_down" value="{{ old('burst_time_down', $package->burst_time_down ?? '') }}" min="0">
                                        <span class="input-group-text">detik</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioritas: <span id="priorityValue">{{ old('priority', $package->priority ?? 4) }}</span></label>
                        <input type="range" class="form-range" id="priority" name="priority" min="1" max="8" value="{{ old('priority', $package->priority ?? 4) }}">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">1 (Tertinggi)</small>
                            <small class="text-muted">8 (Terendah)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Validity & Quota --}}
    <div class="card mb-3">
        <div class="card-header">
            <i class="bi bi-clock me-2"></i>Validitas & Kuota
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Validitas</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="validity_type" id="validity_unlimited" value="unlimited" {{ old('validity_type', $package->validity_type ?? 'unlimited') == 'unlimited' ? 'checked' : '' }}>
                            <label class="form-check-label" for="validity_unlimited">Unlimited</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="validity_type" id="validity_limited" value="limited" {{ old('validity_type', $package->validity_type ?? '') == 'limited' ? 'checked' : '' }}>
                            <label class="form-check-label" for="validity_limited">Terbatas</label>
                        </div>
                    </div>

                    <div id="validityFields" class="mt-3" style="display: none;">
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" class="form-control @error('validity_value') is-invalid @enderror" name="validity_value" value="{{ old('validity_value', $package->validity_value ?? '') }}" placeholder="Jumlah" min="1">
                                @error('validity_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <select class="form-select @error('validity_unit') is-invalid @enderror" name="validity_unit">
                                    <option value="hours" {{ old('validity_unit', $package->validity_unit ?? '') == 'hours' ? 'selected' : '' }}>Jam</option>
                                    <option value="days" {{ old('validity_unit', $package->validity_unit ?? 'days') == 'days' ? 'selected' : '' }}>Hari</option>
                                    <option value="months" {{ old('validity_unit', $package->validity_unit ?? '') == 'months' ? 'selected' : '' }}>Bulan</option>
                                </select>
                                @error('validity_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tipe Limit</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="limit_type" id="limit_unlimited" value="unlimited" {{ old('limit_type', $package->limit_type ?? 'unlimited') == 'unlimited' ? 'checked' : '' }}>
                            <label class="form-check-label" for="limit_unlimited">Unlimited</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="limit_type" id="limit_quota" value="quota" {{ old('limit_type', $package->limit_type ?? '') == 'quota' ? 'checked' : '' }}>
                            <label class="form-check-label" for="limit_quota">Kuota</label>
                        </div>
                    </div>

                    <div id="quotaField" class="mt-3" style="display: none;">
                        <div class="input-group">
                            <input type="number" class="form-control @error('quota') is-invalid @enderror" name="quota" value="{{ old('quota', $package->quota ?? '') }}" placeholder="Kuota" min="1">
                            <span class="input-group-text">MB</span>
                        </div>
                        @error('quota')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pool_name" class="form-label">Nama Pool IP</label>
                    <input type="text" class="form-control @error('pool_name') is-invalid @enderror" id="pool_name" name="pool_name" value="{{ old('pool_name', $package->pool_name ?? '') }}" placeholder="Contoh: pool-hotspot">
                    @error('pool_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dns_servers" class="form-label">DNS Servers</label>
                    <input type="text" class="form-control @error('dns_servers') is-invalid @enderror" id="dns_servers" name="dns_servers" value="{{ old('dns_servers', $package->dns_servers ?? '') }}" placeholder="Contoh: 8.8.8.8,8.8.4.4">
                    @error('dns_servers')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('packages.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ isset($package) ? 'Perbarui' : 'Simpan' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Priority slider
    const prioritySlider = document.getElementById('priority');
    const priorityValue = document.getElementById('priorityValue');
    prioritySlider.addEventListener('input', function () {
        priorityValue.textContent = this.value;
    });

    // Validity type toggle
    const validityRadios = document.querySelectorAll('input[name="validity_type"]');
    const validityFields = document.getElementById('validityFields');

    function toggleValidityFields() {
        const checked = document.querySelector('input[name="validity_type"]:checked');
        validityFields.style.display = checked && checked.value === 'limited' ? 'block' : 'none';
    }

    validityRadios.forEach(radio => radio.addEventListener('change', toggleValidityFields));
    toggleValidityFields();

    // Limit type toggle
    const limitRadios = document.querySelectorAll('input[name="limit_type"]');
    const quotaField = document.getElementById('quotaField');

    function toggleQuotaField() {
        const checked = document.querySelector('input[name="limit_type"]:checked');
        quotaField.style.display = checked && checked.value === 'quota' ? 'block' : 'none';
    }

    limitRadios.forEach(radio => radio.addEventListener('change', toggleQuotaField));
    toggleQuotaField();
</script>
@endpush
