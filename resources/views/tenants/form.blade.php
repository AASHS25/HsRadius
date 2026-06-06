@extends('layouts.app')

@section('title', isset($tenant) ? 'Edit Tenant' : 'Tambah Tenant')

@section('content')
<div class="page-header">
    <h3>{{ isset($tenant) ? 'Edit Tenant' : 'Tambah Tenant' }}</h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenant</a></li>
            <li class="breadcrumb-item active">{{ isset($tenant) ? 'Edit' : 'Baru' }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($tenant) ? route('tenants.update', $tenant) : route('tenants.store') }}">
            @csrf
            @isset($tenant) @method('PUT') @endisset

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Tenant</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tenant->name ?? '') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $tenant->slug ?? '') }}" required placeholder="mis: demo-isp">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active'=>'Aktif','trial'=>'Trial','suspended'=>'Suspended'] as $val=>$lbl)
                            <option value="{{ $val }}" {{ old('status', $tenant->status ?? 'trial') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Paket SaaS</label>
                    <select name="plan_id" class="form-select">
                        <option value="">— Tanpa paket —</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" {{ (string) old('plan_id', $tenant->plan_id ?? '') === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->name }} (Rp {{ number_format($p->price, 0, ',', '.') }}/{{ $p->interval }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @unless(isset($tenant))
                <hr>
                <h6 class="fw-bold mb-3">Admin Tenant (akun pertama)</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama Admin</label>
                        <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name') }}" required>
                        @error('admin_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email Admin</label>
                        <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" required>
                        @error('admin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Password Admin</label>
                        <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
                        @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            @endunless

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ isset($tenant) ? 'Simpan Perubahan' : 'Buat Tenant' }}</button>
                <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
