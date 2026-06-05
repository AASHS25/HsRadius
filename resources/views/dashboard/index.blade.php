@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Dashboard</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="text-muted" style="font-size: 0.85rem;">
            <i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row">
    <x-stat-card title="Total Pelanggan" :value="$totalCustomers ?? 0" icon="bi-people-fill" color="blue" />
    <x-stat-card title="User Online" :value="$onlineUsers ?? 0" icon="bi-wifi" color="green" />
    <x-stat-card title="Paket Aktif" :value="$activePackages ?? 0" icon="bi-box-seam-fill" color="orange" />
    <x-stat-card title="Pendapatan Bulan Ini" :value="'Rp ' . number_format($monthlyRevenue ?? 0, 0, ',', '.')" icon="bi-cash-stack" color="purple" />
</div>

{{-- Charts --}}
<div class="row mb-4">
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-activity me-2"></i>Traffic Harian</span>
                <select class="form-select form-select-sm" style="width: auto;" id="trafficPeriod">
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30" selected>30 Hari</option>
                </select>
            </div>
            <div class="card-body">
                <canvas id="trafficChart" height="280"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bar-chart-fill me-2"></i>Pendapatan Bulanan</span>
                <span class="badge bg-primary">{{ date('Y') }}</span>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Tables Row --}}
<div class="row">
    {{-- Recent Activities --}}
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru</span>
                <a href="{{ route('sessions.history') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Reply</th>
                                <th>NAS</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities ?? [] as $activity)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $activity->username }}</span>
                                    </td>
                                    <td>
                                        @if($activity->reply == 'Access-Accept')
                                            <span class="badge bg-success-subtle text-success">Accept</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Reject</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $activity->nasipaddress ?? '-' }}</td>
                                    <td class="text-muted">{{ \Carbon\Carbon::parse($activity->authdate)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Belum ada aktivitas</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Expiring Soon --}}
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Segera Berakhir</span>
                <span class="badge bg-warning text-dark">{{ count($expiringSoon ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Paket</th>
                                <th>Berakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringSoon ?? [] as $customer)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $customer->username }}</span>
                                        <br>
                                        <small class="text-muted">{{ $customer->fullname ?? '' }}</small>
                                    </td>
                                    <td>{{ $customer->package->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($customer->expiry_date), false);
                                        @endphp
                                        @if($daysLeft <= 3)
                                            <span class="badge bg-danger">{{ $daysLeft }} hari</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $daysLeft }} hari</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Tidak ada yang segera berakhir</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Traffic Chart
    const trafficCtx = document.getElementById('trafficChart').getContext('2d');
    const trafficData = @json($trafficData ?? ['labels' => [], 'upload' => [], 'download' => []]);

    new Chart(trafficCtx, {
        type: 'line',
        data: {
            labels: trafficData.labels,
            datasets: [
                {
                    label: 'Upload (MB)',
                    data: trafficData.upload,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                },
                {
                    label: 'Download (MB)',
                    data: trafficData.download,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 15, font: { size: 12 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json($revenueData ?? ['labels' => [], 'values' => []]);

    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: revenueData.values,
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: '#8b5cf6',
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11 },
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
</script>
@endpush
