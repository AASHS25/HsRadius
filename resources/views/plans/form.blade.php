@extends('layouts.app')

@section('title', isset($plan) ? 'Edit Paket' : 'Tambah Paket')

@section('content')
<div class="page-header">
    <h3>{{ isset($plan) ? 'Edit Paket SaaS' : 'Tambah Paket SaaS' }}</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">Paket SaaS</a></li>
            <li class="breadcrumb-item active">{{ isset($plan) ? 'Edit' : 'Baru' }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($plan) ? route('plans.update', $plan) : route('plans.store') }}">
            @csrf
            @isset($plan) @method('PUT') @endisset

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Paket</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $plan->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $plan->price ?? '0') }}" required>
                    @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Interval</label>
                    <select name="interval" class="form-select">
                        @foreach(['monthly'=>'Bulanan','yearly'=>'Tahunan'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('interval', $plan->interval ?? 'monthly') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Maks Pelanggan <span class="text-muted small">(kosong = unlimited)</span></label>
                    <input type="number" name="max_customers" class="form-control" value="{{ old('max_customers', $plan->max_customers ?? '') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Maks NAS <span class="text-muted small">(kosong = unlimited)</span></label>
                    <input type="number" name="max_nas" class="form-control" value="{{ old('max_nas', $plan->max_nas ?? '') }}">
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ isset($plan) ? 'Simpan Perubahan' : 'Buat Paket' }}</button>
                <a href="{{ route('plans.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
