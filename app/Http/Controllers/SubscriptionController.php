<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $invoices = TenantInvoice::with('tenant', 'plan')->latest()->paginate(20);
        $tenants = Tenant::with('plan')->orderBy('name')->get();

        return view('subscriptions.index', compact('invoices', 'tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'period_start' => 'required|date',
            'due_date' => 'required|date',
        ]);

        $tenant = Tenant::with('plan')->findOrFail($validated['tenant_id']);

        if (! $tenant->plan) {
            return back()->with('error', 'Tenant belum punya paket. Set paket dulu lewat Edit Tenant.');
        }

        $start = Carbon::parse($validated['period_start']);
        $end = $tenant->plan->interval === 'yearly'
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();

        TenantInvoice::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $tenant->plan_id,
            'amount' => $tenant->plan->price,
            'period_start' => $start,
            'period_end' => $end,
            'due_date' => $validated['due_date'],
            'status' => 'unpaid',
        ]);

        return redirect()->route('subscriptions.index')->with('success', 'Tagihan tenant berhasil dibuat.');
    }

    public function pay(TenantInvoice $invoice)
    {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        // Reactivate the tenant if it was suspended and has no other overdue bills.
        $tenant = $invoice->tenant;
        if ($tenant && $tenant->status === 'suspended'
            && ! $tenant->tenantInvoices()->where('status', 'unpaid')->whereDate('due_date', '<', now())->exists()) {
            $tenant->update(['status' => 'active']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Tagihan ditandai lunas.');
    }
}
