<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the legacy free-text `plan` column. It is replaced by `plan_id`
     * (FK to plans) + the Tenant::plan() relation, and it was shadowing that
     * relation when accessed as $tenant->plan.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('plan')->nullable()->after('status');
        });
    }
};
