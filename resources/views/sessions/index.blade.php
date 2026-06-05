@extends('layouts.app')

@section('title', 'Sesi Aktif')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>
            Sesi Aktif
            @if(isset($sessions))
                <span class="badge bg-success fs-6">{{ $sessions->total() ?? 0 }}</span>
            @endif
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Sesi Aktif</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sessions.history') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> Riwayat
        </a>
        <button class="btn btn-outline-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
        <form method="POST" action="{{ route('sessions.disconnect-all') }}" class="d-inline" onsubmit="return confirm('PERINGATAN: Semua user aktif akan diputus koneksinya. Lanjutkan?')">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-power me-1"></i> Disconnect All
            </button>
        </form>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('sessions.index') }}">
            <div class="row g-2 align-items-end">
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
                    <label class="form-label small fw-semibold">Cari Username</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari username..." value="{{ request('search') }}">
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

{{-- Auto refresh indicator --}}
<div class="d-flex align-items-center justify-content-end mb-2">
    <small class="text-muted">
        <i class="bi bi-arrow-repeat me-1"></i>
        Auto-refresh setiap 30 detik
        <span id="countdown" class="fw-semibold">30</span>s
    </small>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>MAC Address</th>
                        <th>NAS</th>
                        <th>Mulai Sesi</th>
                        <th>Durasi</th>
                        <th>Upload</th>
                        <th>Download</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions ?? [] as $session)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $session->username }}</span>
                            </td>
                            <td>
                                <code>{{ $session->framedipaddress ?? '-' }}</code>
                            </td>
                            <td>
                                <small class="text-muted font-monospace">{{ $session->callingstationid ?? '-' }}</small>
                            </td>
                            <td>{{ $session->nasipaddress ?? '-' }}</td>
                            <td>
                                <small>
                                    @if($session->acctstarttime)
                                        {{ \Carbon\Carbon::parse($session->acctstarttime)->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    {{ $session->formatted_session_time ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-success">
                                    <i class="bi bi-arrow-up"></i> {{ $session->formatted_upload ?? '0 B' }}
                                </span>
                            </td>
                            <td>
                                <span class="text-primary">
                                    <i class="bi bi-arrow-down"></i> {{ $session->formatted_download ?? '0 B' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('sessions.disconnect', $session->username) }}" class="d-inline" onsubmit="return confirm('Disconnect user {{ $session->username }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Disconnect">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-wifi-off" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">Tidak ada sesi aktif saat ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($sessions) && $sessions->hasPages())
        <div class="card-footer bg-white">
            {{ $sessions->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh every 30 seconds
    var countdownValue = 30;
    var countdownEl = document.getElementById('countdown');

    var refreshInterval = setInterval(function () {
        countdownValue--;
        if (countdownEl) countdownEl.textContent = countdownValue;
        if (countdownValue <= 0) {
            clearInterval(refreshInterval);
            location.reload();
        }
    }, 1000);
</script>
@endpush
