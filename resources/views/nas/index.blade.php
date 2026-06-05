@extends('layouts.app')

@section('title', 'Manajemen NAS/Router')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Manajemen NAS/Router</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">NAS/Router</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('nas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah NAS
    </a>
</div>

<div class="row">
    @forelse($nasList ?? [] as $nas)
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; display: flex; align-items: center; justify-content: center;" class="me-3">
                                <i class="bi bi-router" style="font-size: 1.3rem; color: #0284c7;"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $nas->shortname ?: $nas->nasname }}</h6>
                                <small class="text-muted">{{ $nas->nasname }}</small>
                            </div>
                        </div>
                        <div>
                            @if($nas->is_active ?? true)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="border rounded p-2" style="background: #f8fafc;">
                                    <div class="fw-bold text-primary" style="font-size: 1.2rem;">{{ $nas->online_users_count ?? 0 }}</div>
                                    <small class="text-muted">Online</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2" style="background: #f8fafc;">
                                    <div class="fw-semibold" style="font-size: 0.85rem;">{{ ucfirst($nas->type ?? 'MikroTik') }}</div>
                                    <small class="text-muted">Tipe</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2" style="background: #f8fafc;">
                                    <div class="fw-semibold" style="font-size: 0.85rem;">{{ $nas->api_port ?? '-' }}</div>
                                    <small class="text-muted">Port API</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($nas->description)
                        <p class="text-muted small mb-3">{{ Str::limit($nas->description, 80) }}</p>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('nas.edit', $nas->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-info flex-fill btn-test-connection" data-id="{{ $nas->id }}">
                            <i class="bi bi-plug me-1"></i> Test
                        </button>
                        <form method="POST" action="{{ route('nas.destroy', $nas->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus NAS ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-router" style="font-size: 3rem; color: #94a3b8;"></i>
                    <p class="text-muted mt-2 mb-3">Belum ada NAS/Router yang terdaftar</p>
                    <a href="{{ route('nas.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah NAS
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

{{-- Test Connection Modal --}}
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plug me-2"></i>Test Koneksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="testResult">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Menghubungkan ke router...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.btn-test-connection').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const modal = new bootstrap.Modal(document.getElementById('testModal'));
            document.getElementById('testResult').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Menghubungkan ke router...</p></div>';
            modal.show();

            fetch('/nas/' + id + '/test-connection', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    document.getElementById('testResult').innerHTML =
                        '<div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>Koneksi berhasil!</div>' +
                        (data.info ? '<pre class="bg-light p-3 rounded small">' + JSON.stringify(data.info, null, 2) + '</pre>' : '');
                } else {
                    document.getElementById('testResult').innerHTML =
                        '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + (data.message || 'Koneksi gagal') + '</div>';
                }
            })
            .catch(function () {
                document.getElementById('testResult').innerHTML =
                    '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Gagal menghubungi server</div>';
            });
        });
    });
</script>
@endpush
