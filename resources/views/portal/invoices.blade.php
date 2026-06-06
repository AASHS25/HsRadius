@extends('portal.layout')

@section('title', 'Tagihan')

@section('content')
<h4 class="mb-3">Tagihan Saya</h4>

<div class="card"><div class="card-body p-0"><div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>No. Invoice</th><th>Paket</th><th>Jumlah</th><th>Jatuh Tempo</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->package->name ?? '-' }}</td>
                    <td>Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td>{{ $inv->due_date?->format('d/m/Y') }}</td>
                    <td>
                        @if($inv->status === 'paid')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum bayar</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($inv->status !== 'paid')
                            <form method="POST" action="{{ route('portal.invoices.pay', $inv) }}">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="bi bi-qr-code me-1"></i>Bayar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tagihan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div></div>
@if($invoices->hasPages())<div class="card-footer">{{ $invoices->links() }}</div>@endif
</div>
@endsection
