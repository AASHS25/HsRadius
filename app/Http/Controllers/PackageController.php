<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\RadiusService;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    protected RadiusService $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    /**
     * Display a listing of packages.
     */
    public function index(Request $request)
    {
        $query = Package::withCount('customers');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $packages = $query->latest()->paginate(15)->withQueryString();

        return view('packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('packages.create');
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:packages,name',
            'description' => 'nullable|string|max:500',
            'rate_up' => 'required|integer|min:1',
            'rate_up_unit' => 'required|in:k,M',
            'rate_down' => 'required|integer|min:1',
            'rate_down_unit' => 'required|in:k,M',
            'burst_up' => 'nullable|integer|min:1',
            'burst_up_unit' => 'nullable|in:k,M',
            'burst_down' => 'nullable|integer|min:1',
            'burst_down_unit' => 'nullable|in:k,M',
            'burst_threshold_up' => 'nullable|integer|min:1',
            'burst_threshold_up_unit' => 'nullable|in:k,M',
            'burst_threshold_down' => 'nullable|integer|min:1',
            'burst_threshold_down_unit' => 'nullable|in:k,M',
            'burst_time_up' => 'nullable|integer|min:1',
            'burst_time_down' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'validity_value' => 'required|integer|min:1',
            'validity_unit' => 'required|in:hours,days,months',
            'service_type' => 'required|in:hotspot,pppoe,both',
            'shared_users' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $package = Package::create($validated);

        $this->radiusService->syncPackage($package);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    /**
     * Display the specified package.
     */
    public function show(Package $package)
    {
        $package->loadCount('customers');

        return view('packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => "required|string|max:255|unique:packages,name,{$package->id}",
            'description' => 'nullable|string|max:500',
            'rate_up' => 'required|integer|min:1',
            'rate_up_unit' => 'required|in:k,M',
            'rate_down' => 'required|integer|min:1',
            'rate_down_unit' => 'required|in:k,M',
            'burst_up' => 'nullable|integer|min:1',
            'burst_up_unit' => 'nullable|in:k,M',
            'burst_down' => 'nullable|integer|min:1',
            'burst_down_unit' => 'nullable|in:k,M',
            'burst_threshold_up' => 'nullable|integer|min:1',
            'burst_threshold_up_unit' => 'nullable|in:k,M',
            'burst_threshold_down' => 'nullable|integer|min:1',
            'burst_threshold_down_unit' => 'nullable|in:k,M',
            'burst_time_up' => 'nullable|integer|min:1',
            'burst_time_down' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'validity_value' => 'required|integer|min:1',
            'validity_unit' => 'required|in:hours,days,months',
            'service_type' => 'required|in:hotspot,pppoe,both',
            'shared_users' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);

        $this->radiusService->syncPackage($package);

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Remove the specified package.
     */
    public function destroy(Package $package)
    {
        if ($package->customers()->exists()) {
            return redirect()->route('packages.index')
                ->with('error', 'Paket tidak dapat dihapus karena masih digunakan oleh pelanggan.');
        }

        $this->radiusService->deletePackage($package);

        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}
