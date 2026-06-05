@extends('layouts.app')

@section('title', 'Riwayat Sesi')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h3>Riwayat Sesi</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sessions.index') }}">Sesi Aktif</a></li>
                <li class="breadcrumb-item active">Riwayat</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('sessions.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-wifi me-1"></i> Sesi Aktif
    </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('sessions.history') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Cari Username</label>
                    <input type="text" name="search" class="form-control" placeholder="Cari username..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from', now()->subDays(7)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-2">
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
                        <th>Username</th>
                        <th>IP Address</th>
                        <th>NAS</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Durasi</th>
                        <th>Upload</th>
                        <th>Download</th>
                        <th>Terminate Cause</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions ?? [] as $session)
                        <tr>
                            <td><span class="fw-medium">{{ $session->username }}</span></td>
                            <td><code>{{ $session->framedipaddress ?? '-' }}</code></td>
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
                                <small>
                                    @if($session->acctstoptime)
                                        {{ \Carbon\Carbon::parse($session->acctstoptime)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
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
                                @if($session->acctterminatecause)
                                    @switch($session->acctterminatecause)
                                        @case('User-Request')
                                            <span class="badge bg-info-subtle text-info">{{ $session->acctterminatecause }}</span>
                                            @break
                                        @case('Admin-Reset')
                                        @case('Admin-Reboot')
                                            <span class="badge bg-warning-subtle text-warning">{{ $session->acctterminatecause }}</span>
                                            @break
                                        @case('Session-Timeout')
                                        @case('Idle-Timeout')
                                            <span class="badge bg-secondary">{{ $session->acctterminatecause }}</span>
                                            @break
                                        @case('Lost-Carrier')
                                        @case('Port-Error')
                                            <span class="badge bg-danger-subtle text-danger">{{ $session->acctterminatecause }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark border">{{ $session->acctterminatecause }}</span>
                                    @endswitch
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                                <p class="mb-0 mt-2">Belum ada riwayat sesi untuk periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($sessions) && $sessions->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <small class="text-muted">
                    Menampilkan {{ $sessions->firstItem() }} - {{ $sessions->lastItem() }} dari {{ $sessions->total() }} sesi
                </small>
                {{ $sessions->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
