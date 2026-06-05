<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->unique();
            $table->string('password', 128);
            $table->string('fullname', 128);
            $table->string('email', 128)->nullable();
            $table->string('phone', 32)->nullable();
            $table->text('address')->nullable();
            $table->enum('service_type', ['hotspot', 'pppoe'])->default('hotspot');
            $table->foreignId('package_id')->constrained('packages')->onDelete('restrict');
            $table->foreignId('nas_id')->nullable()->constrained('nas')->onDelete('set null');
            $table->enum('status', ['active', 'suspended', 'expired', 'disabled'])->default('active');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->string('static_ip', 15)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->text('notes')->nullable();
            $table->string('pppoe_caller_id', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
