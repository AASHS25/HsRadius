<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['radcheck', 'radreply', 'radgroupcheck', 'radgroupreply', 'radusergroup'];

    /**
     * Run the migrations.
     *
     * Only the RADIUS tables the app provisions get a tenant_id. radacct and
     * radpostauth are written by FreeRADIUS and are scoped at read-time via the
     * customer username instead (see BelongsToTenantViaUsername).
     */
    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')
                    ->constrained('tenants')->nullOnDelete();
            });
        }

        // Backfill existing rows for live upgrades.
        // username-keyed tables map via customers; group-keyed via packages.
        foreach (['radcheck', 'radreply', 'radusergroup'] as $name) {
            foreach (DB::table($name)->whereNull('tenant_id')->get() as $row) {
                $tenantId = DB::table('customers')->where('username', $row->username)->value('tenant_id');
                if ($tenantId) {
                    DB::table($name)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                }
            }
        }

        foreach (['radgroupcheck', 'radgroupreply'] as $name) {
            foreach (DB::table($name)->whereNull('tenant_id')->get() as $row) {
                $packageId = (int) str_replace('pkg-', '', (string) $row->groupname);
                $tenantId = DB::table('packages')->where('id', $packageId)->value('tenant_id');
                if ($tenantId) {
                    DB::table($name)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
                }
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
