<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Voucher;
use App\Services\RadiusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    protected RadiusService $radiusService;

    public function __construct(RadiusService $radiusService)
    {
        $this->radiusService = $radiusService;
    }

    /**
     * Display a listing of vouchers with filters.
     */
    public function index(Request $request)
    {
        $query = Voucher::with('package');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->input('package_id'));
        }

        if ($request->filled('batch_name')) {
            $query->where('batch_name', $request->input('batch_name'));
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->input('search') . '%');
        }

        $vouchers = $query->latest()->paginate(20)->withQueryString();
        $packages = Package::where('is_active', true)->get();
        $batches = Voucher::select('batch_name')->distinct()->whereNotNull('batch_name')->pluck('batch_name');

        return view('vouchers.index', compact('vouchers', 'packages', 'batches'));
    }

    /**
     * Show the form for generating vouchers.
     */
    public function createBatch()
    {
        $packages = Package::where('is_active', true)->get();

        return view('vouchers.generate', compact('packages'));
    }

    /**
     * Generate a batch of vouchers.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'quantity' => 'required|integer|min:1|max:500',
            'code_length' => 'required|integer|min:4|max:20',
            'prefix' => 'nullable|string|max:10|alpha_num',
            'validity_value' => 'required|integer|min:1',
            'validity_unit' => 'required|in:hours,days,months',
            'batch_name' => 'nullable|string|max:255',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $batchName = $validated['batch_name'] ?? 'Batch-' . now()->format('Ymd-His');

        $generatedVouchers = [];

        DB::transaction(function () use ($validated, $package, $batchName, &$generatedVouchers) {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $code = Voucher::generateCode(
                    $validated['code_length'],
                    $validated['prefix'] ?? ''
                );

                $voucher = Voucher::create([
                    'code' => $code,
                    'package_id' => $package->id,
                    'status' => 'unused',
                    'validity_value' => $validated['validity_value'],
                    'validity_unit' => $validated['validity_unit'],
                    'batch_name' => $batchName,
                ]);

                $generatedVouchers[] = $voucher;
            }
        });

        return redirect()->route('vouchers.index', ['batch_name' => $batchName])
            ->with('success', count($generatedVouchers) . ' voucher berhasil dibuat.');
    }

    /**
     * Display a printable view of selected vouchers.
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'voucher_ids' => 'required_without:batch_name|array',
            'voucher_ids.*' => 'exists:vouchers,id',
            'batch_name' => 'required_without:voucher_ids|string',
        ]);

        if ($request->filled('batch_name')) {
            $vouchers = Voucher::with('package')
                ->where('batch_name', $request->input('batch_name'))
                ->get();
        } else {
            $vouchers = Voucher::with('package')
                ->whereIn('id', $validated['voucher_ids'])
                ->get();
        }

        $template = in_array($request->input('template'), ['default', 'card', 'thermal'])
            ? $request->input('template') : 'default';

        return view('vouchers.print', compact('vouchers', 'template'));
    }

    /**
     * Show a single voucher.
     */
    public function show(Voucher $voucher)
    {
        $voucher->load('package');

        return view('vouchers.show', compact('voucher'));
    }

    /**
     * Delete a single voucher.
     */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->status === 'used') {
            return redirect()->route('vouchers.index')
                ->with('error', 'Voucher yang sudah digunakan tidak dapat dihapus.');
        }

        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher berhasil dihapus.');
    }

    /**
     * Delete an entire batch of vouchers.
     */
    public function destroyBatch(Request $request)
    {
        $validated = $request->validate([
            'batch_name' => 'required|string',
        ]);

        $usedCount = Voucher::where('batch_name', $validated['batch_name'])
            ->where('status', 'used')
            ->count();

        if ($usedCount > 0) {
            return redirect()->route('vouchers.index')
                ->with('error', "Batch tidak dapat dihapus karena {$usedCount} voucher sudah digunakan.");
        }

        $deleted = Voucher::where('batch_name', $validated['batch_name'])->delete();

        return redirect()->route('vouchers.index')
            ->with('success', "{$deleted} voucher dalam batch berhasil dihapus.");
    }

    /**
     * Delete multiple selected vouchers (used ones are skipped).
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'integer',
        ]);

        $vouchers = Voucher::whereIn('id', $validated['voucher_ids'])->get();
        $deletable = $vouchers->where('status', '!=', 'used');
        $usedCount = $vouchers->count() - $deletable->count();

        foreach ($deletable as $voucher) {
            $voucher->delete();
        }

        $message = $deletable->count() . ' voucher berhasil dihapus.';
        if ($usedCount > 0) {
            $message .= " {$usedCount} voucher terpakai dilewati.";
        }

        return redirect()->route('vouchers.index')->with('success', $message);
    }
}
