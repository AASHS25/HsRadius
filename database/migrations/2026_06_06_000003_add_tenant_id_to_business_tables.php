<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['customers', 'packages', 'vouchers', 'invoices', 'nas'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')
                    ->constrained('tenants')->nullOnDelete();
            });
        }

        // Backfill existing rows to a tenant when upgrading live single-tenant data.
        $hasData = collect($this->tables)->contains(fn ($t) => DB::table($t)->exists());

        if ($hasData) {
            $tenantId = DB::table('tenants')->min('id');

            if (! $tenantId) {
                $tenantId = DB::table('tenants')->insertGetId([
                    'name' => 'Default',
                    'slug' => 'default',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($this->tables as $name) {
                DB::table($name)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
