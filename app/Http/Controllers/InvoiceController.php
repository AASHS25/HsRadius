<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Services\RadiusService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'package']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('fullname', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest()->paginate(20)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::with('package')->where('status', 'active')->orderBy('fullname')->get();
        $packages = Package::where('is_active', true)->get();

        return view('invoices.form', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'nullable|exists:packages,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['invoice_number'] = Invoice::generateNumber();
        $validated['status'] = 'unpaid';

        if (empty($validated['package_id'])) {
            $customer = Customer::findOrFail($validated['customer_id']);
            $validated['package_id'] = $customer->package_id;
        }

        Invoice::create($validated);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        return redirect()->route('invoices.edit', $invoice);
    }

    public function edit(Invoice $invoice)
    {
        $customers = Customer::where('status', 'active')->orderBy('fullname')->get();
        $packages = Package::where('is_active', true)->get();

        return view('invoices.form', compact('invoice', 'customers', 'packages'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')
                ->with('error', 'Invoice yang sudah dibayar tidak dapat diedit.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'nullable|exists:packages,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.index')
                ->with('error', 'Invoice yang sudah dibayar tidak dapat dihapus.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dihapus.');
    }

    public function markPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $invoice->amount,
            'paid_date' => now(),
            'payment_method' => 'manual',
        ]);

        $this->reactivateAfterPayment($invoice);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice lunas. Pelanggan diaktifkan & masa aktif diperpanjang.');
    }

    /**
     * After payment, release isolir: reactivate the customer and extend the
     * subscription by the package validity. Reused by the payment webhook.
     */
    public function reactivateAfterPayment(Invoice $invoice): void
    {
        $customer = $invoice->customer;
        if (! $customer) {
            return;
        }

        $package = $invoice->package ?: $customer->package;
        $base = ($customer->expiry_date && $customer->expiry_date->isFuture())
            ? $customer->expiry_date->copy()
            : now();

        if ($package && $package->validity_type === 'limited') {
            $customer->expiry_date = match ($package->validity_unit) {
                'minutes' => $base->addMinutes($package->validity_value),
                'hours' => $base->addHours($package->validity_value),
                'days' => $base->addDays($package->validity_value),
                'months' => $base->addMonths($package->validity_value),
                default => $customer->expiry_date,
            };
        }

        $customer->status = 'active';
        $customer->save();

        app(RadiusService::class)->activateCustomer($customer);
    }
}
