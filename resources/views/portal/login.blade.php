@extends('portal.layout')

@section('title', 'Masuk Portal')

@section('content')
<div class="row justify-content-center" style="margin-top: 6vh;">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-4">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#3b82f6,#2563eb);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:1.8rem;">
                <i class="bi bi-person-circle"></i>
            </div>
            <h4 class="mt-3 mb-0">Portal Pelanggan</h4>
            <p class="text-muted small">Masuk dengan akun internet Anda</p>
        </div>
        <div class="card"><div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('portal.login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Username</label>
                    <input name="username" class="form-control" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Masuk</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
