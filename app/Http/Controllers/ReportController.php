<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Nas;
use App\Models\Package;
use App\Models\RadAcct;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function traffic(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $nasFilter = $request->input('nas');

        $query = RadAcct::where('acctstarttime', '>=', $from)
            ->where('acctstarttime', '<=', $to . ' 23:59:59');

        if ($nasFilter) {
            $query->where('nasipaddress', $nasFilter);
        }

        $daily = (clone $query)
            ->selectRaw('DATE(acctstarttime) as date')
            ->selectRaw('SUM(acctinputoctets) as upload_bytes')
            ->selectRaw('SUM(acctoutputoctets) as download_bytes')
            ->selectRaw('COUNT(*) as sessions')
            ->selectRaw('COUNT(DISTINCT username) as users')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dailyData = $daily->map(fn($d) => [
            'date' => Carbon::parse($d->date)->format('d/m/Y'),
            'upload' => $this->formatBytes($d->upload_bytes),
            'download' => $this->formatBytes($d->download_bytes),
            'upload_mb' => round($d->upload_bytes / 1048576, 2),
            'download_mb' => round($d->download_bytes / 1048576, 2),
            'sessions' => $d->sessions,
            'users' => $d->users,
        ])->toArray();

        $totalUpload = $this->formatBytes($daily->sum('upload_bytes'));
        $totalDownload = $this->formatBytes($daily->sum('download_bytes'));
        $totalSessions = $daily->sum('sessions');
        $nasList = Nas::where('is_active', true)->get();

        return view('reports.traffic', compact('dailyData', 'totalUpload', 'totalDownload', 'totalSessions', 'nasList'));
    }

    public function revenue(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $paid = Invoice::where('status', 'paid')
            ->whereBetween('paid_date', [$from, $to])
            ->selectRaw('DATE(paid_date) as period')
            ->selectRaw('SUM(amount) as paid')
            ->selectRaw('COUNT(*) as paid_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $revenueData = $paid->map(fn($r) => [
            'period' => Carbon::parse($r->period)->format('d/m/Y'),
            'paid' => (float) $r->paid,
            'paid_count' => $r->paid_count,
            'unpaid_count' => 0,
        ])->toArray();

        $totalPaid = $paid->sum('paid');
        $totalUnpaid = Invoice::where('status', 'unpaid')->sum('amount');
        $totalInvoices = Invoice::whereBetween('created_at', [$from, $to . ' 23:59:59'])->count();

        return view('reports.revenue', compact('revenueData', 'totalPaid', 'totalUnpaid', 'totalInvoices'));
    }

    public function customers()
    {
        $statusCounts = Customer::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $packageDistribution = Package::withCount('customers')
            ->get()
            ->map(fn($p) => [
                'name' => $p->name,
                'type' => $p->type,
                'count' => $p->customers_count,
            ])->toArray();

        $monthlyGrowth = Customer::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();

        return view('reports.customers', compact('statusCounts', 'packageDistribution', 'monthlyGrowth'));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $v = (float) $bytes;
        while ($v >= 1024 && $i < count($units) - 1) {
            $v /= 1024;
            $i++;
        }
        return round($v, 2) . ' ' . $units[$i];
    }
}
