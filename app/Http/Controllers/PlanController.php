<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->orderBy('price')->get();

        return view('plans.index', compact('plans'));
    }

    public function create()
    {
        return view('plans.form');
    }

    public function store(Request $request)
    {
        Plan::create($this->validated($request));

        return redirect()->route('plans.index')->with('success', 'Paket SaaS berhasil dibuat.');
    }

    public function edit(Plan $plan)
    {
        return view('plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validated($request));

        return redirect()->route('plans.index')->with('success', 'Paket SaaS berhasil diperbarui.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->exists()) {
            return redirect()->route('plans.index')
                ->with('error', 'Paket masih dipakai tenant, tidak bisa dihapus.');
        }

        $plan->delete();

        return redirect()->route('plans.index')->with('success', 'Paket SaaS dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:monthly,yearly',
            'max_customers' => 'nullable|integer|min:0',
            'max_nas' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
