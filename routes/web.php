<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NasController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard (all authenticated roles)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Landlord — tenant management (super-admin only)
    Route::middleware('can:manage-tenants')->group(function () {
        Route::resource('tenants', TenantController::class)->except(['show']);
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');

        // SaaS billing (landlord)
        Route::resource('plans', PlanController::class)->except(['show']);
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::post('subscriptions/{invoice}/pay', [SubscriptionController::class, 'pay'])->name('subscriptions.pay');
    });

    // Customers (admin + operator)
    Route::middleware('can:manage-customers')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/suspend', [CustomerController::class, 'suspend'])->name('customers.suspend');
        Route::post('customers/{customer}/activate', [CustomerController::class, 'activate'])->name('customers.activate');
        Route::post('customers/{customer}/disconnect', [CustomerController::class, 'disconnect'])->name('customers.disconnect');
    });

    // Packages (admin only)
    Route::middleware('can:manage-packages')->group(function () {
        Route::resource('packages', PackageController::class);
    });

    // NAS/Router (admin only)
    Route::middleware('can:manage-nas')->group(function () {
        Route::resource('nas', NasController::class);
        Route::post('nas/{na}/test-connection', [NasController::class, 'testConnection'])->name('nas.test-connection');
        Route::get('nas/{na}/status', [NasController::class, 'status'])->name('nas.status');
    });

    // Vouchers (admin + operator)
    Route::middleware('can:manage-vouchers')->group(function () {
        Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('vouchers/generate', [VoucherController::class, 'createBatch'])->name('vouchers.generate');
        Route::post('vouchers/generate', [VoucherController::class, 'generate'])->name('vouchers.store');
        Route::post('vouchers/print', [VoucherController::class, 'print'])->name('vouchers.print');
        Route::delete('vouchers/bulk-delete', [VoucherController::class, 'bulkDelete'])->name('vouchers.bulk-delete');
        Route::delete('vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
        Route::delete('vouchers/batch/{batchName}', [VoucherController::class, 'destroyBatch'])->name('vouchers.destroy-batch');
    });

    // Sessions (admin + operator)
    Route::middleware('can:manage-sessions')->group(function () {
        Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::get('sessions/history', [SessionController::class, 'history'])->name('sessions.history');
        Route::post('sessions/{username}/disconnect', [SessionController::class, 'disconnect'])->name('sessions.disconnect');
        Route::post('sessions/disconnect-all', [SessionController::class, 'disconnectAll'])->name('sessions.disconnect-all');
    });

    // Invoices / billing (admin only)
    Route::middleware('can:manage-billing')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'markPaid'])->name('invoices.pay');
    });

    // Reports (admin + operator)
    Route::middleware('can:view-reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('traffic', [ReportController::class, 'traffic'])->name('traffic');
        Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('customers', [ReportController::class, 'customers'])->name('customers');
    });
});

// Customer self-service portal (separate "customer" guard)
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('login', [CustomerPortalController::class, 'showLogin'])->name('login');
    Route::post('login', [CustomerPortalController::class, 'login']);

    Route::middleware('auth:customer')->group(function () {
        Route::get('/', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::post('renew', [CustomerPortalController::class, 'renew'])->name('renew');
        Route::post('logout', [CustomerPortalController::class, 'logout'])->name('logout');
    });
});
