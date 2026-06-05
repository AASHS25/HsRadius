@extends('layouts.app')

@section('title', 'Laporan Pendapatan')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Laporan Pendapatan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Laporan</li>
                <li class="breadcrumb-item active">Pendapatan</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.revenue') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', now()->startOfYear()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tampilan</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="view_mode" id="viewDaily" value="daily" {{ request('view_mode', 'monthly') == 'daily' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="viewDaily">Harian</label>
                        <input type="radio" class="btn-check" name="view_mode" id="viewMonthly" value="monthly" {{ request('view_mode', 'monthly') == 'monthly' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="viewMonthly">Bulanan</label>
                    </div>
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
        <div class="card h-100" style="border-left: 4px solid #22c55e;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Pendapatan</div>
                        <h4 class="mb-0 mt-1 text-success">Rp {{ number_format($totalPaid ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(34, 197, 94, 0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-stack" style="font-size: 1.4rem; color: #22c55e;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100" style="border-left: 4px solid #f59e0b;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Belum Dibayar</div>
                        <h4 class="mb-0 mt-1 text-warning">Rp {{ number_format($totalUnpaid ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history" style="font-size: 1.4rem; color: #f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100" style="border-left: 4px solid #8b5cf6;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Invoice</div>
                        <h4 class="mb-0 mt-1">{{ number_format($totalInvoices ?? 0) }}</h4>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-receipt" style="font-size: 1.4rem; color: #8b5cf6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-bar-chart-fill me-2"></i>Grafik Pendapatan</span>
    </div>
    <div class="card-body">
        <canvas id="revenueChart" height="100"></canvas>
    </div>
</div>

{{-- Detail Table --}}
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2"></i>Detail Pendapatan
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Pendapatan</th>
                        <th>Invoice Lunas</th>
                        <th>Invoice Belum Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueData ?? [] as $row)
                        <tr>
                            <td><span class="fw-medium">{{ $row['period'] }}</span></td>
                            <td>
                                <span class="fw-semibold text-success">Rp {{ number_format($row['paid'] ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">{{ $row['paid_count'] ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning">{{ $row['unpaid_count'] ?? 0 }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-bar-chart" style="font-size: 2rem;"></i>
                                <p class="mb-0 mt-2">Tidak ada data pendapatan untuk periode ini</p>
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
    var revenueData = @json($revenueData ?? []);

    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: revenueData.map(function(d) { return d.period; }),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: revenueData.map(function(d) { return d.paid || 0; }),
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                borderColor: '#22c55e',
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
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
                            if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                            return 'Rp ' + value;
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
