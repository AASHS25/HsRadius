@extends('portal.layout')

@section('title', 'Dashboard')

@section('content')
<h4 class="mb-3">Halo, {{ $customer->fullname }} 👋</h4>

<div class="row g-3">
    <div class="col-md-3 col-6"><div class="card"><div class="card-body">
        <div class="text-muted small">Status</div>
        <div class="fw-bold">
            @if($customer->status === 'active')
                <span class="text-success">Aktif</span>
            @elseif($customer->status === 'expired')
                <span class="text-danger">Expired</span>
            @else
                <span class="text-warning">{{ ucfirst($customer->status) }}</span>
            @endif
        </div>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="card"><div class="card-body">
        <div class="text-muted small">Koneksi</div>
        <div class="fw-bold">{!! $online ? '<span class="text-success">Online</span>' : '<span class="text-secondary">Offline</span>' !!}</div>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="card"><div class="card-body">
        <div class="text-muted small">Paket</div>
        <div class="fw-bold">{{ $customer->package->name ?? '-' }}</div>
    </div></div></div>
    <div class="col-md-3 col-6"><div class="card"><div class="card-body">
        <div class="text-muted small">Masa Aktif s/d</div>
        <div class="fw-bold">{{ $customer->expiry_date?->format('d/m/Y') ?? '-' }}</div>
    </div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6"><div class="card"><div class="card-body">
        <div class="text-muted small mb-1">Pemakaian Bulan Ini</div>
        <div>Upload: <b>{{ number_format(($usage->up ?? 0) / 1048576, 2) }} MB</b></div>
        <div>Download: <b>{{ number_format(($usage->down ?? 0) / 1048576, 2) }} MB</b></div>
    </div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <div class="text-muted small">Tagihan belum dibayar</div>
            <div class="fw-bold fs-5">{{ $unpaid }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.invoices') }}" class="btn btn-outline-primary btn-sm">Lihat Tagihan</a>
            <form method="POST" action="{{ route('portal.renew') }}">
                @csrf
                <button class="btn btn-primary btn-sm">Perpanjang</button>
            </form>
        </div>
    </div></div></div>
</div>
@endsection
