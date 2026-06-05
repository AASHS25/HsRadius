@extends('layouts.app')

@section('title', 'Paket Layanan')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Paket Layanan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Paket Layanan</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('packages.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Paket
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Nama Paket</th>
                        <th>Tipe</th>
                        <th>Upload</th>
                        <th>Download</th>
                        <th>Burst</th>
                        <th>Harga</th>
                        <th>Validitas</th>
                        <th>Pelanggan</th>
                        <th>Status</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages ?? [] as $index => $package)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>
                                <span class="fw-medium">{{ $package->name }}</span>
                                @if($package->description)
                                    <br><small class="text-muted">{{ Str::limit($package->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($package->type == 'hotspot')
                                    <span class="badge bg-info-subtle text-info">Hotspot</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">PPPoE</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $rateUp = $package->rate_up ?? 0;
                                    $upDisplay = $rateUp >= 1000 ? ($rateUp / 1000) . ' Mbps' : $rateUp . ' Kbps';
                                @endphp
                                <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $upDisplay }}</span>
                            </td>
                            <td>
                                @php
                                    $rateDown = $package->rate_down ?? 0;
                                    $downDisplay = $rateDown >= 1000 ? ($rateDown / 1000) . ' Mbps' : $rateDown . ' Kbps';
                                @endphp
                                <span class="text-primary"><i class="bi bi-arrow-down"></i> {{ $downDisplay }}</span>
                            </td>
                            <td>
                                @if($package->burst_rate_down || $package->burst_rate_up)
                                    @php
                                        $burstDown = $package->burst_rate_down ?? 0;
                                        $burstDisplay = $burstDown >= 1000 ? ($burstDown / 1000) . ' Mbps' : $burstDown . ' Kbps';
                                    @endphp
                                    <span class="text-warning"><i class="bi bi-lightning"></i> {{ $burstDisplay }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">Rp {{ number_format($package->price ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if($package->validity_type == 'unlimited')
                                    <span class="badge bg-success-subtle text-success">Unlimited</span>
                                @else
                                    {{ $package->validity_value ?? '-' }}
                                    @switch($package->validity_unit ?? '')
                                        @case('hours') Jam @break
                                        @case('days') Hari @break
                                        @case('months') Bulan @break
                                        @default {{ $package->validity_unit ?? '' }}
                                    @endswitch
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="bi bi-people me-1"></i>{{ $package->customers_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                @if($package->is_active ?? true)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('packages.destroy', $package->id) }}" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket ini?')">
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
                            <td colspan="11" class="text-center text-muted py-5">
                                <i class="bi bi-box" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">Belum ada paket layanan</p>
                                <a href="{{ route('packages.create') }}" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-lg me-1"></i> Tambah Paket
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
