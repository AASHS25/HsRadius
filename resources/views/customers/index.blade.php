@extends('layouts.app')

@section('title', 'Manajemen Pelanggan')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Manajemen Pelanggan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pelanggan</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.export') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i> Export</a>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-upload me-1"></i> Import</button>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pelanggan
        </a>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Pelanggan (CSV)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Kolom: <code>username,password,fullname,email,phone,service_type,package_id,status,expiry_date</code>. Tip: Export dulu sebagai contoh format.</p>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Username, nama, email..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspend</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Tipe Layanan</label>
                    <select name="service_type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="hotspot" {{ request('service_type') == 'hotspot' ? 'selected' : '' }}>Hotspot</option>
                        <option value="pppoe" {{ request('service_type') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Paket</label>
                    <select name="package_id" class="form-select">
                        <option value="">Semua Paket</option>
                        @foreach($packages ?? [] as $package)
                            <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
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
                        <th style="width: 40px;">#</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Paket</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Masa Aktif</th>
                        <th style="width: 60px;">Online</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers ?? [] as $index => $customer)
                        <tr>
                            <td class="text-muted">{{ ($customers->currentPage() - 1) * $customers->perPage() + $index + 1 }}</td>
                            <td>
                                <span class="fw-medium">{{ $customer->username }}</span>
                            </td>
                            <td>
                                {{ $customer->fullname ?? '-' }}
                                @if($customer->email)
                                    <br><small class="text-muted">{{ $customer->email }}</small>
                                @endif
                            </td>
                            <td>{{ $customer->package->name ?? '-' }}</td>
                            <td>
                                @if($customer->service_type == 'hotspot')
                                    <span class="badge bg-info-subtle text-info">Hotspot</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">PPPoE</span>
                                @endif
                            </td>
                            <td>
                                @switch($customer->status)
                                    @case('active')
                                        <span class="badge bg-success">Aktif</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-warning text-dark">Suspend</span>
                                        @break
                                    @case('expired')
                                        <span class="badge bg-danger">Expired</span>
                                        @break
                                    @case('disabled')
                                        <span class="badge bg-secondary">Nonaktif</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $customer->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                @if($customer->expiry_date)
                                    <small>{{ \Carbon\Carbon::parse($customer->start_date)->format('d/m/Y') }}</small>
                                    <br>
                                    <small class="text-muted">s/d {{ \Carbon\Carbon::parse($customer->expiry_date)->format('d/m/Y') }}</small>
                                @else
                                    <small class="text-muted">Unlimited</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->is_online ?? false)
                                    <span class="d-inline-block rounded-circle bg-success" style="width: 10px; height: 10px;" title="Online"></span>
                                @else
                                    <span class="d-inline-block rounded-circle bg-secondary" style="width: 10px; height: 10px; opacity: 0.3;" title="Offline"></span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($customer->status == 'active')
                                        <form method="POST" action="{{ route('customers.suspend', $customer->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin men-suspend pelanggan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning" title="Suspend">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                    @elseif($customer->status == 'suspended')
                                        <form method="POST" action="{{ route('customers.activate', $customer->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin mengaktifkan kembali pelanggan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" title="Aktifkan">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($customer->is_online ?? false)
                                        <form method="POST" action="{{ route('customers.disconnect', $customer->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin memutus koneksi pelanggan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" title="Disconnect">
                                                <i class="bi bi-wifi-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.')">
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
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-people" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">Belum ada data pelanggan</p>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Pelanggan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($customers) && $customers->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted">
                    Menampilkan {{ $customers->firstItem() }} - {{ $customers->lastItem() }} dari {{ $customers->total() }} pelanggan
                </small>
                {{ $customers->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
