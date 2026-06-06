<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reseller (tenant) prepaid balance + a ledger of top-ups/deductions.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('balance', 14, 2)->default(0)->after('plan_id');
        });

        Schema::create('tenant_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);          // signed: + top-up, - deduction
            $table->string('type')->default('topup');  // topup | deduct
            $table->string('description')->nullable();
            $table->decimal('balance_after', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_balance_transactions');
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
