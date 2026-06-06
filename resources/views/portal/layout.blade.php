<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') - HsRadius</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .portal-nav { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .portal-nav .brand { font-weight: 700; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    </style>
</head>
<body>
@auth('customer')
    <nav class="portal-nav py-2 mb-4">
        <div class="container d-flex align-items-center justify-content-between">
            <span class="brand"><i class="bi bi-broadcast me-1"></i> HsRadius Portal</span>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('portal.dashboard') }}" class="text-white text-decoration-none small">Dashboard</a>
                <a href="{{ route('portal.invoices') }}" class="text-white text-decoration-none small">Tagihan</a>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-light">Keluar</button>
                </form>
            </div>
        </div>
    </nav>
@endauth

<div class="container pb-5">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info">{{ session('info') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
