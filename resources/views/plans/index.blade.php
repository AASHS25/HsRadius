@extends('layouts.app')

@section('title', 'Paket SaaS')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Paket SaaS</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Landlord</li>
                <li class="breadcrumb-item active">Paket SaaS</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('plans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah Paket</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Nama</th><th>Harga</th><th>Interval</th>
                        <th>Maks Pelanggan</th><th>Status</th><th>Tenant</th>
                        <th style="width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $i => $plan)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $plan->name }}</td>
                            <td>Rp {{ number_format($plan->price, 0, ',', '.') }}</td>
                            <td>{{ $plan->interval }}</td>
                            <td>{{ $plan->max_customers ?? 'Unlimited' }}</td>
                            <td>
                                @if($plan->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $plan->tenants_count }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('plans.destroy', $plan) }}" onsubmit="return confirm('Hapus paket ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada paket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
