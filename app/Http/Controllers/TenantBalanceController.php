<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantBalanceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantBalanceController extends Controller
{
    /** Landlord: manage every tenant's prepaid balance. */
    public function index()
    {
        $tenants = Tenant::orderBy('name')->get();
        $transactions = TenantBalanceTransaction::with('tenant')->latest()->limit(30)->get();

        return view('balance.index', compact('tenants', 'transactions'));
    }

    public function topUp(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:topup,deduct',
            'description' => 'nullable|string|max:255',
        ]);

        $tenant = Tenant::findOrFail($data['tenant_id']);
        $signed = $data['type'] === 'deduct' ? -abs($data['amount']) : abs($data['amount']);
        $tenant->adjustBalance($signed, $data['type'], $data['description'] ?? null);

        return back()->with('success', 'Saldo tenant "'.$tenant->name.'" diperbarui.');
    }

    /** Tenant admin: view own balance + history. */
    public function mine()
    {
        $tenant = Auth::user()->tenant;
        abort_unless($tenant, 404);

        $transactions = $tenant->balanceTransactions()->limit(50)->get();

        return view('balance.mine', compact('tenant', 'transactions'));
    }
}
