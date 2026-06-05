@extends('layouts.app')

@section('title', isset($nas) ? 'Edit NAS' : 'Tambah NAS')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>{{ isset($nas) ? 'Edit NAS/Router' : 'Tambah NAS/Router' }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('nas.index') }}">NAS/Router</a></li>
                <li class="breadcrumb-item active">{{ isset($nas) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
    </div>
</div>

<form method="POST" action="{{ isset($nas) ? route('nas.update', $nas->id) : route('nas.store') }}">
    @csrf
    @if(isset($nas))
        @method('PUT')
    @endif

    <div class="row">
        {{-- Informasi NAS --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-hdd-network me-2"></i>Informasi NAS
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="nasname" class="form-label">IP Address / Hostname <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nasname') is-invalid @enderror" id="nasname" name="nasname" value="{{ old('nasname', $nas->nasname ?? '') }}" required placeholder="Contoh: 192.168.1.1">
                        @error('nasname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="shortname" class="form-label">Nama Pendek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('shortname') is-invalid @enderror" id="shortname" name="shortname" value="{{ old('shortname', $nas->shortname ?? '') }}" required placeholder="Contoh: MikroTik-Main">
                        @error('shortname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="secret" class="form-label">RADIUS Secret <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('secret') is-invalid @enderror" id="secret" name="secret" value="{{ old('secret', $nas->secret ?? '') }}" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('secret')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Tipe</label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                            <option value="mikrotik" {{ old('type', $nas->type ?? 'mikrotik') == 'mikrotik' ? 'selected' : '' }}>MikroTik</option>
                            <option value="cisco" {{ old('type', $nas->type ?? '') == 'cisco' ? 'selected' : '' }}>Cisco</option>
                            <option value="other" {{ old('type', $nas->type ?? '') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Deskripsi router...">{{ old('description', $nas->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $nas->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">NAS Aktif</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- MikroTik API --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-router me-2"></i>MikroTik API</span>
                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#apiSection" role="button" aria-expanded="{{ old('api_host', $nas->api_host ?? '') ? 'true' : 'false' }}">
                        <i class="bi bi-chevron-down"></i>
                    </a>
                </div>
                <div class="collapse {{ old('api_host', $nas->api_host ?? '') || old('api_username', $nas->api_username ?? '') ? 'show' : '' }}" id="apiSection">
                    <div class="card-body">
                        <div class="alert alert-info small py-2 mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Konfigurasi API diperlukan untuk manajemen user otomatis dan disconnect user secara langsung dari panel.
                        </div>

                        <div class="mb-3">
                            <label for="api_host" class="form-label">API Host</label>
                            <input type="text" class="form-control @error('api_host') is-invalid @enderror" id="api_host" name="api_host" value="{{ old('api_host', $nas->api_host ?? '') }}" placeholder="Kosongkan jika sama dengan IP NAS">
                            @error('api_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_port" class="form-label">API Port</label>
                            <input type="number" class="form-control @error('api_port') is-invalid @enderror" id="api_port" name="api_port" value="{{ old('api_port', $nas->api_port ?? 8728) }}">
                            @error('api_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_username" class="form-label">API Username</label>
                            <input type="text" class="form-control @error('api_username') is-invalid @enderror" id="api_username" name="api_username" value="{{ old('api_username', $nas->api_username ?? '') }}" placeholder="admin">
                            @error('api_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="api_password" class="form-label">API Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('api_password') is-invalid @enderror" id="api_password" name="api_password" value="{{ old('api_password', $nas->api_password ?? '') }}">
                                <button class="btn btn-outline-secondary" type="button" id="toggleApiPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('api_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="api_ssl" value="0">
                                <input type="checkbox" class="form-check-input" id="api_ssl" name="api_ssl" value="1" {{ old('api_ssl', $nas->api_ssl ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="api_ssl">Gunakan SSL (Port 8729)</label>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-info w-100" id="btnTestConnection">
                            <i class="bi bi-plug me-1"></i> Test Koneksi
                        </button>
                        <div id="testConnectionResult" class="mt-2"></div>
                    </div>
                </div>
                <div class="card-body collapse-placeholder {{ old('api_host', $nas->api_host ?? '') || old('api_username', $nas->api_username ?? '') ? 'd-none' : '' }}" id="apiPlaceholder">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-router" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2 small">Klik tombol di atas untuk mengkonfigurasi MikroTik API</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('nas.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ isset($nas) ? 'Perbarui' : 'Simpan' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Toggle secret visibility
    function setupToggle(btnId, inputId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        if (btn && input) {
            btn.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }
    }

    setupToggle('toggleSecret', 'secret');
    setupToggle('toggleApiPassword', 'api_password');

    // Collapse placeholder toggle
    var apiSection = document.getElementById('apiSection');
    var apiPlaceholder = document.getElementById('apiPlaceholder');
    if (apiSection && apiPlaceholder) {
        apiSection.addEventListener('show.bs.collapse', function () {
            apiPlaceholder.classList.add('d-none');
        });
        apiSection.addEventListener('hide.bs.collapse', function () {
            apiPlaceholder.classList.remove('d-none');
        });
    }

    // Test Connection
    var btnTest = document.getElementById('btnTestConnection');
    if (btnTest) {
        btnTest.addEventListener('click', function () {
            var resultDiv = document.getElementById('testConnectionResult');
            var originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan...';
            this.disabled = true;

            var formData = {
                api_host: document.getElementById('api_host').value || document.getElementById('nasname').value,
                api_port: document.getElementById('api_port').value,
                api_username: document.getElementById('api_username').value,
                api_password: document.getElementById('api_password').value,
                api_ssl: document.getElementById('api_ssl').checked ? 1 : 0
            };

            fetch('/nas/test-connection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success small py-2 mb-0"><i class="bi bi-check-circle me-1"></i> Koneksi berhasil!</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger small py-2 mb-0"><i class="bi bi-x-circle me-1"></i> ' + (data.message || 'Koneksi gagal') + '</div>';
                }
            })
            .catch(function () {
                resultDiv.innerHTML = '<div class="alert alert-danger small py-2 mb-0"><i class="bi bi-x-circle me-1"></i> Gagal menghubungi server</div>';
            })
            .finally(function () {
                btnTest.innerHTML = originalHtml;
                btnTest.disabled = false;
            });
        });
    }
</script>
@endpush
