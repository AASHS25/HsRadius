@extends('layouts.app')

@section('title', 'Kelola Tenant')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Kelola Tenant</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Landlord</li>
                <li class="breadcrumb-item active">Tenant</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('tenants.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Tenant
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Slug</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Pelanggan</th>
                        <th style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $i => $tenant)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $tenant->name }}</td>
                            <td><code>{{ $tenant->slug }}</code></td>
                            <td>{{ $tenant->plan?->name ?? '-' }}</td>
                            <td>
                                @switch($tenant->status)
                                    @case('active') <span class="badge bg-success">Aktif</span> @break
                                    @case('trial') <span class="badge bg-info">Trial</span> @break
                                    @case('suspended') <span class="badge bg-danger">Suspended</span> @break
                                    @default <span class="badge bg-secondary">{{ $tenant->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $tenant->users_count }}</td>
                            <td>{{ $tenant->customers_count }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    @if($tenant->status === 'suspended')
                                        <form method="POST" action="{{ route('tenants.activate', $tenant) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Aktifkan"><i class="bi bi-play-circle"></i></button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('tenants.suspend', $tenant) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning" title="Suspend"><i class="bi bi-pause-circle"></i></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}" class="d-inline" onsubmit="return confirm('Yakin hapus tenant ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada tenant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
