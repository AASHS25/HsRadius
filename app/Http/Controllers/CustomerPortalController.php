<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\RadAcct;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Self-service portal for end customers. They authenticate against the
 * customers table (cleartext password, as required by RADIUS) using a
 * dedicated "customer" guard.
 */
class CustomerPortalController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('username', $data['username'])->first();

        if (! $customer || ! hash_equals((string) $customer->password, (string) $data['password'])) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard');
    }

    public function dashboard()
    {
        $customer = Auth::guard('customer')->user()->load('package', 'nas');

        $online = RadAcct::where('username', $customer->username)->whereNull('acctstoptime')->exists();

        $usage = RadAcct::where('username', $customer->username)
            ->whereMonth('acctstarttime', now()->month)
            ->whereYear('acctstarttime', now()->year)
            ->selectRaw('COALESCE(SUM(acctinputoctets),0) as up, COALESCE(SUM(acctoutputoctets),0) as down')
            ->first();

        $unpaid = Invoice::where('customer_id', $customer->id)->where('status', 'unpaid')->count();

        return view('portal.dashboard', compact('customer', 'online', 'usage', 'unpaid'));
    }

    public function invoices()
    {
        $customer = Auth::guard('customer')->user();

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('package')->latest()->paginate(10);

        return view('portal.invoices', compact('customer', 'invoices'));
    }

    public function pay(Invoice $invoice)
    {
        $customer = Auth::guard('customer')->user();

        if ($invoice->customer_id !== $customer->id) {
            abort(403);
        }
        if ($invoice->status === 'paid') {
            return redirect()->route('portal.invoices')->with('info', 'Tagihan ini sudah lunas.');
        }

        $url = app(PaymentService::class)->createCheckoutUrl($invoice);

        if (! $url) {
            return redirect()->route('portal.invoices')
                ->with('error', 'Pembayaran online belum diaktifkan oleh admin. Silakan hubungi admin.');
        }

        return redirect()->away($url);
    }

    public function renew()
    {
        $customer = Auth::guard('customer')->user()->load('package');

        if (Invoice::where('customer_id', $customer->id)->where('status', 'unpaid')->exists()) {
            return redirect()->route('portal.invoices')->with('info', 'Masih ada tagihan yang belum dibayar.');
        }

        Invoice::create([
            'tenant_id' => $customer->tenant_id,
            'invoice_number' => Invoice::generateNumber(),
            'customer_id' => $customer->id,
            'package_id' => $customer->package_id,
            'amount' => $customer->package->price ?? 0,
            'status' => 'unpaid',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        return redirect()->route('portal.invoices')->with('success', 'Tagihan perpanjangan dibuat. Silakan lakukan pembayaran.');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
