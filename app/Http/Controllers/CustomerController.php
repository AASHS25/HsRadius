<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Nas;
use App\Models\Package;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    protected RadiusService $radiusService;
    protected MikrotikService $mikrotikService;

    public function __construct(RadiusService $radiusService, MikrotikService $mikrotikService)
    {
        $this->radiusService = $radiusService;
        $this->mikrotikService = $mikrotikService;
    }

    public function index(Request $request)
    {
        $query = Customer::with('package', 'nas');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->input('package_id'));
        }

        $customers = $query->latest()->paginate(20)->withQueryString();
        $packages = Package::where('is_active', true)->get();

        return view('customers.index', compact('customers', 'packages'));
    }

    public function export()
    {
        $customers = Customer::with('package')->get();
        $filename = 'pelanggan-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($customers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['username', 'password', 'fullname', 'email', 'phone', 'service_type', 'package_id', 'status', 'expiry_date']);
            foreach ($customers as $c) {
                fputcsv($out, [$c->username, $c->password, $c->fullname, $c->email, $c->phone, $c->service_type, $c->package_id, $c->status, optional($c->expiry_date)->toDateString()]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (! is_array($header) || count($row) !== count($header)) {
                $skipped++;
                continue;
            }
            $data = array_combine($header, $row);
            $username = trim($data['username'] ?? '');
            $packageId = $data['package_id'] ?? null;

            $dupe = $username !== '' && Customer::withoutGlobalScopes()->where('username', $username)->exists();
            $validPackage = $packageId && Package::where('id', $packageId)->exists();

            if ($username === '' || $dupe || ! $validPackage) {
                $skipped++;
                continue;
            }

            $customer = Customer::create([
                'username' => $username,
                'password' => ($data['password'] ?? '') ?: Str::upper(Str::random(8)),
                'fullname' => ($data['fullname'] ?? '') ?: $username,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'service_type' => in_array($data['service_type'] ?? '', ['hotspot', 'pppoe']) ? $data['service_type'] : 'hotspot',
                'package_id' => $packageId,
                'status' => 'active',
                'start_date' => now(),
            ]);
            $this->radiusService->createCustomer($customer);
            $created++;
        }
        fclose($handle);

        return redirect()->route('customers.index')->with('success', "$created pelanggan diimpor, $skipped dilewati.");
    }

    public function create()
    {
        $packages = Package::where('is_active', true)->get();
        $nasList = Nas::where('is_active', true)->get();

        return view('customers.form', compact('packages', 'nasList'));
    }

    public function store(Request $request)
    {
        // Enforce the tenant's SaaS plan customer limit.
        $tenant = app(\App\Tenancy\CurrentTenant::class)->get();
        if ($tenant && $tenant->plan && $tenant->plan->max_customers !== null
            && Customer::count() >= $tenant->plan->max_customers) {
            return back()->withInput()->with('error',
                "Batas pelanggan paket \"{$tenant->plan->name}\" ({$tenant->plan->max_customers}) sudah tercapai. Upgrade paket untuk menambah pelanggan.");
        }

        $validated = $request->validate([
            'fullname' => 'required|string|max:128',
            'username' => 'required|string|max:64|unique:customers,username',
            'password' => 'required|string|min:4|max:128',
            'email' => 'nullable|email|max:128',
            'phone' => 'nullable|string|max:32',
            'address' => 'nullable|string',
            'package_id' => 'required|exists:packages,id',
            'nas_id' => 'nullable|exists:nas,id',
            'service_type' => 'required|in:hotspot,pppoe',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'static_ip' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'pppoe_caller_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        $validated['start_date'] = $validated['start_date'] ?? now();

        $package = Package::find($validated['package_id']);
        if (!isset($validated['expiry_date']) && $package && $package->validity_type === 'limited') {
            $validated['expiry_date'] = match ($package->validity_unit) {
                'minutes' => now()->addMinutes($package->validity_value),
                'hours' => now()->addHours($package->validity_value),
                'days' => now()->addDays($package->validity_value),
                'months' => now()->addMonths($package->validity_value),
                default => null,
            };
        }

        $customer = Customer::create($validated);
        $this->radiusService->createCustomer($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load('package', 'nas');
        return view('customers.form', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $packages = Package::where('is_active', true)->get();
        $nasList = Nas::where('is_active', true)->get();

        return view('customers.form', compact('customer', 'packages', 'nasList'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:128',
            'username' => "required|string|max:64|unique:customers,username,{$customer->id}",
            'password' => 'nullable|string|min:4|max:128',
            'email' => 'nullable|email|max:128',
            'phone' => 'nullable|string|max:32',
            'address' => 'nullable|string',
            'package_id' => 'required|exists:packages,id',
            'nas_id' => 'nullable|exists:nas,id',
            'service_type' => 'required|in:hotspot,pppoe',
            'status' => 'nullable|in:active,suspended,expired,disabled',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'static_ip' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'pppoe_caller_id' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $customer->update($validated);
        $this->radiusService->createCustomer($customer->fresh());

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $this->radiusService->deleteCustomer($customer->username);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function suspend(Customer $customer)
    {
        $this->radiusService->suspendCustomer($customer->username);
        $customer->update(['status' => 'suspended']);

        if ($customer->nas) {
            $this->mikrotikService->disconnectUser($customer->nas, $customer->username, $customer->service_type);
        }

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$customer->fullname} berhasil disuspend.");
    }

    public function activate(Customer $customer)
    {
        $this->radiusService->activateCustomer($customer);
        $customer->update(['status' => 'active']);

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$customer->fullname} berhasil diaktifkan.");
    }

    public function disconnect(Customer $customer)
    {
        if ($customer->nas) {
            $this->mikrotikService->disconnectUser($customer->nas, $customer->username, $customer->service_type);
        }
        $this->radiusService->disconnectUser($customer->username);

        return redirect()->route('customers.index')
            ->with('success', "Pelanggan {$customer->fullname} berhasil di-disconnect.");
    }
}
