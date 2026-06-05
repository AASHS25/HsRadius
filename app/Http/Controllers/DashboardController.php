<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Nas;
use App\Models\Package;
use App\Models\RadAcct;
use App\Models\RadPostAuth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = Customer::count();
        $onlineUsers = RadAcct::online()->count();
        $activePackages = Package::where('is_active', true)->count();

        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', Carbon::now()->month)
            ->whereYear('paid_date', Carbon::now()->year)
            ->sum('amount');

        $recentActivities = RadPostAuth::orderBy('authdate', 'desc')->take(10)->get();

        $expiringSoon = Customer::where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(7))
            ->with('package')
            ->get();

        $trafficData = $this->getTrafficChartData();
        $revenueData = $this->getRevenueChartData();

        return view('dashboard.index', compact(
            'totalCustomers',
            'onlineUsers',
            'activePackages',
            'monthlyRevenue',
            'recentActivities',
            'expiringSoon',
            'trafficData',
            'revenueData'
        ));
    }

    protected function getTrafficChartData(): array
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $traffic = RadAcct::where('acctstarttime', '>=', $startDate)
            ->selectRaw('DATE(acctstarttime) as date')
            ->selectRaw('SUM(acctinputoctets) as total_upload')
            ->selectRaw('SUM(acctoutputoctets) as total_download')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $upload = [];
        $download = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d M');

            $dayData = $traffic->firstWhere('date', $date);
            $upload[] = $dayData ? round($dayData->total_upload / 1048576, 2) : 0;
            $download[] = $dayData ? round($dayData->total_download / 1048576, 2) : 0;
        }

        return compact('labels', 'upload', 'download');
    }

    protected function getRevenueChartData(): array
    {
        $labels = [];
        $revenue = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $revenue[] = (float) Invoice::where('status', 'paid')
                ->whereMonth('paid_date', $date->month)
                ->whereYear('paid_date', $date->year)
                ->sum('amount');
        }

        return compact('labels', 'revenue');
    }
}
