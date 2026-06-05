@extends('layouts.app')

@section('title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>{{ isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Pelanggan</a></li>
                <li class="breadcrumb-item active">{{ isset($customer) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
    </div>
</div>

<form method="POST" action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}">
    @csrf
    @if(isset($customer))
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left Column: Info Pelanggan --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Informasi Pelanggan
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $customer->username ?? '') }}" required {{ isset($customer) ? 'readonly' : '' }}>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(isset($customer))
                            <small class="text-muted">Username tidak dapat diubah setelah dibuat.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password {{ isset($customer) ? '' : '<span class="text-danger">*</span>' }}</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ isset($customer) ? '' : 'required' }}>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" id="generatePassword" title="Generate Password">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @if(isset($customer))
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="fullname" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('fullname') is-invalid @enderror" id="fullname" name="fullname" value="{{ old('fullname', $customer->fullname ?? '') }}">
                        @error('fullname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $customer->email ?? '') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Layanan --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>Pengaturan Layanan
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="service_type" class="form-label">Tipe Layanan <span class="text-danger">*</span></label>
                            <select class="form-select @error('service_type') is-invalid @enderror" id="service_type" name="service_type" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="hotspot" {{ old('service_type', $customer->service_type ?? '') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                                <option value="pppoe" {{ old('service_type', $customer->service_type ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                            </select>
                            @error('service_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active" {{ old('status', $customer->status ?? 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="suspended" {{ old('status', $customer->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspend</option>
                                <option value="disabled" {{ old('status', $customer->status ?? '') == 'disabled' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="package_id" class="form-label">Paket Layanan <span class="text-danger">*</span></label>
                            <select class="form-select @error('package_id') is-invalid @enderror" id="package_id" name="package_id" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($packages ?? [] as $package)
                                    <option value="{{ $package->id }}" {{ old('package_id', $customer->package_id ?? '') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('package_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nas_id" class="form-label">NAS/Router</label>
                            <select class="form-select @error('nas_id') is-invalid @enderror" id="nas_id" name="nas_id">
                                <option value="">-- Pilih NAS --</option>
                                @foreach($nasList ?? [] as $nas)
                                    <option value="{{ $nas->id }}" {{ old('nas_id', $customer->nas_id ?? '') == $nas->id ? 'selected' : '' }}>
                                        {{ $nas->shortname }} ({{ $nas->nasname }})
                                    </option>
                                @endforeach
                            </select>
                            @error('nas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', isset($customer) ? ($customer->start_date ? \Carbon\Carbon::parse($customer->start_date)->format('Y-m-d') : '') : date('Y-m-d')) }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="expiry_date" class="form-label">Tanggal Berakhir</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', isset($customer) && $customer->expiry_date ? \Carbon\Carbon::parse($customer->expiry_date)->format('Y-m-d') : '') }}">
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="static_ip" class="form-label">IP Statis</label>
                        <input type="text" class="form-control @error('static_ip') is-invalid @enderror" id="static_ip" name="static_ip" value="{{ old('static_ip', $customer->static_ip ?? '') }}" placeholder="Contoh: 10.10.10.2">
                        @error('static_ip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mac_address" class="form-label">MAC Address</label>
                        <input type="text" class="form-control @error('mac_address') is-invalid @enderror" id="mac_address" name="mac_address" value="{{ old('mac_address', $customer->mac_address ?? '') }}" placeholder="Contoh: AA:BB:CC:DD:EE:FF">
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0" id="pppoeFields" style="display: none;">
                        <label for="pppoe_caller_id" class="form-label">PPPoE Caller ID</label>
                        <input type="text" class="form-control @error('pppoe_caller_id') is-invalid @enderror" id="pppoe_caller_id" name="pppoe_caller_id" value="{{ old('pppoe_caller_id', $customer->pppoe_caller_id ?? '') }}">
                        @error('pppoe_caller_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Identifier unik untuk koneksi PPPoE pelanggan.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="card mb-3">
        <div class="card-body">
            <label for="notes" class="form-label">Catatan</label>
            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Catatan tambahan untuk pelanggan ini...">{{ old('notes', $customer->notes ?? '') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Buttons --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ isset($customer) ? 'Perbarui' : 'Simpan' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Toggle PPPoE fields visibility
    const serviceType = document.getElementById('service_type');
    const pppoeFields = document.getElementById('pppoeFields');

    function togglePppoeFields() {
        pppoeFields.style.display = serviceType.value === 'pppoe' ? 'block' : 'none';
    }

    serviceType.addEventListener('change', togglePppoeFields);
    togglePppoeFields();

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // Generate random password
    document.getElementById('generatePassword').addEventListener('click', function () {
        const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        let password = '';
        for (let i = 0; i < 10; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        passwordInput.value = password;
        passwordInput.setAttribute('type', 'text');
        const icon = togglePassword.querySelector('i');
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    });
</script>
@endpush
