@extends('layouts.app')

@section('title', 'Laporan Traffic')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Laporan Traffic</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Laporan</li>
                <li class="breadcrumb-item active">Traffic</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.traffic') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', now()->subDays(30)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">NAS/Router</label>
                    <select name="nas" class="form-select">
                        <option value="">Semua NAS</option>
                        @foreach($nasList ?? [] as $nas)
                            <option value="{{ $nas->nasname }}" {{ request('nas') == $nas->nasname ? 'selected' : '' }}>
                                {{ $nas->shortname }} ({{ $nas->nasname }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100" style="border-left: 4px solid #3b82f6;">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Total Upload</div>
                <h4 class="mb-0 mt-1 text-primary">{{ $totalUpload ?? '0 B' }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100" style="border-left: 4px solid #22c55e;">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Total Download</div>
                <h4 class="mb-0 mt-1 text-success">{{ $totalDownload ?? '0 B' }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100" style="border-left: 4px solid #06b6d4;">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-semibold">Total Sesi</div>
                <h4 class="mb-0 mt-1">{{ number_format($totalSessions ?? 0) }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-activity me-2"></i>Grafik Traffic Harian</span>
    </div>
    <div class="card-body">
        <canvas id="trafficChart" height="100"></canvas>
    </div>
</div>

{{-- Daily Table --}}
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2"></i>Detail Traffic Harian
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Upload</th>
                        <th>Download</th>
                        <th>Sesi</th>
                        <th>User Unik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyData ?? [] as $day)
                        <tr>
                            <td><span class="fw-medium">{{ $day['date'] }}</span></td>
                            <td>
                                <span class="text-primary"><i class="bi bi-arrow-up"></i> {{ $day['upload'] }}</span>
                            </td>
                            <td>
                                <span class="text-success"><i class="bi bi-arrow-down"></i> {{ $day['download'] }}</span>
                            </td>
                            <td>{{ number_format($day['sessions']) }}</td>
                            <td>{{ number_format($day['users']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Tidak ada data traffic untuk periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var ctx = document.getElementById('trafficChart').getContext('2d');
    var dailyData = @json($dailyData ?? []);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(function(d) { return d.date; }),
            datasets: [
                {
                    label: 'Upload (MB)',
                    data: dailyData.map(function(d) { return d.upload_mb || 0; }),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                },
                {
                    label: 'Download (MB)',
                    data: dailyData.map(function(d) { return d.download_mb || 0; }),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.08)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 15, font: { size: 12 } }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
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
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
</script>
@endpush
