<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount(['users', 'customers'])->orderBy('name')->get();

        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.form', ['plans' => Plan::where('is_active', true)->orderBy('price')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|max:255|unique:tenants,slug',
            'status' => 'required|in:active,trial,suspended',
            'plan_id' => 'nullable|exists:plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'status' => $validated['status'],
                'plan_id' => $validated['plan_id'] ?? null,
            ]);

            User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => $validated['admin_password'], // hashed by the model cast
                'tenant_id' => $tenant->id,
                'role' => User::ROLE_ADMIN,
            ]);
        });

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant "'.$validated['name'].'" beserta admin-nya berhasil dibuat.');
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.form', [
            'tenant' => $tenant,
            'plans' => Plan::where('is_active', true)->orderBy('price')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|max:255|unique:tenants,slug,'.$tenant->id,
            'status' => 'required|in:active,trial,suspended',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.index')->with('success', 'Tenant berhasil diperbarui.');
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);

        return redirect()->route('tenants.index')->with('success', 'Tenant "'.$tenant->name.'" disuspend.');
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);

        return redirect()->route('tenants.index')->with('success', 'Tenant "'.$tenant->name.'" diaktifkan.');
    }

    public function destroy(Tenant $tenant)
    {
        if (Customer::where('tenant_id', $tenant->id)->exists()) {
            return redirect()->route('tenants.index')
                ->with('error', 'Tenant tidak bisa dihapus karena masih punya pelanggan. Hapus/pindahkan pelanggan dulu.');
        }

        DB::transaction(function () use ($tenant) {
            User::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
        });

        return redirect()->route('tenants.index')->with('success', 'Tenant berhasil dihapus.');
    }
}
