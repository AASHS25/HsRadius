@extends('layouts.app')

@section('title', 'Laporan Pelanggan')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Laporan Pelanggan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Laporan</li>
                <li class="breadcrumb-item active">Pelanggan</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Status Summary Cards --}}
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card h-100" style="border-left: 4px solid #22c55e;">
            <div class="card-body text-center">
                <h3 class="text-success mb-0">{{ $statusCounts['active'] ?? 0 }}</h3>
                <small class="text-muted text-uppercase fw-semibold">Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card h-100" style="border-left: 4px solid #f59e0b;">
            <div class="card-body text-center">
                <h3 class="text-warning mb-0">{{ $statusCounts['suspended'] ?? 0 }}</h3>
                <small class="text-muted text-uppercase fw-semibold">Suspend</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card h-100" style="border-left: 4px solid #ef4444;">
            <div class="card-body text-center">
                <h3 class="text-danger mb-0">{{ $statusCounts['expired'] ?? 0 }}</h3>
                <small class="text-muted text-uppercase fw-semibold">Expired</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card h-100" style="border-left: 4px solid #6b7280;">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $statusCounts['disabled'] ?? 0 }}</h3>
                <small class="text-muted text-uppercase fw-semibold">Nonaktif</small>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row mb-4">
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>Status Pelanggan
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="max-width: 300px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-graph-up-arrow me-2"></i>Pertumbuhan Pelanggan (12 Bulan Terakhir)
            </div>
            <div class="card-body">
                <canvas id="growthChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Package Distribution Table --}}
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2"></i>Distribusi Pelanggan Per Paket
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Paket</th>
                        <th>Tipe</th>
                        <th>Jumlah Pelanggan</th>
                        <th style="width: 35%;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = collect($packageDistribution ?? [])->sum('count');
                    @endphp
                    @forelse($packageDistribution ?? [] as $dist)
                        <tr>
                            <td><span class="fw-medium">{{ $dist['name'] }}</span></td>
                            <td>
                                @if(($dist['type'] ?? '') == 'hotspot')
                                    <span class="badge bg-info-subtle text-info">Hotspot</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">PPPoE</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $dist['count'] }}</span>
                                <small class="text-muted">pelanggan</small>
                            </td>
                            <td>
                                @php
                                    $percent = $total > 0 ? round($dist['count'] / $total * 100, 1) : 0;
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <small class="fw-semibold" style="min-width: 45px;">{{ $percent }}%</small>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-box" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Tidak ada data distribusi paket</p>
                            </td>
                        </tr>
                    @endforelse
                    @if($total > 0)
                        <tr class="table-light">
                            <td class="fw-bold" colspan="2">Total</td>
                            <td class="fw-bold">{{ $total }}</td>
                            <td class="fw-bold">100%</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Status Pie Chart
    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Suspend', 'Expired', 'Nonaktif'],
            datasets: [{
                data: [
                    {{ $statusCounts['active'] ?? 0 }},
                    {{ $statusCounts['suspended'] ?? 0 }},
                    {{ $statusCounts['expired'] ?? 0 }},
                    {{ $statusCounts['disabled'] ?? 0 }}
                ],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#6b7280'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            }
        }
    });

    // Monthly Growth Chart
    var monthlyGrowth = @json($monthlyGrowth ?? []);

    new Chart(document.getElementById('growthChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: monthlyGrowth.map(function(d) { return d.month; }),
            datasets: [{
                label: 'Pelanggan Baru',
                data: monthlyGrowth.map(function(d) { return d.count; }),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.08)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
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
                            return context.parsed.y + ' pelanggan baru';
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
                        stepSize: 1
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
