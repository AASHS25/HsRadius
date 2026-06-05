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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['hotspot', 'pppoe'])->default('hotspot');
            $table->integer('rate_up')->comment('Upload speed in Kbps');
            $table->integer('rate_down')->comment('Download speed in Kbps');
            $table->integer('burst_up')->nullable();
            $table->integer('burst_down')->nullable();
            $table->integer('burst_threshold_up')->nullable();
            $table->integer('burst_threshold_down')->nullable();
            $table->integer('burst_time_up')->nullable();
            $table->integer('burst_time_down')->nullable();
            $table->integer('priority')->default(8);
            $table->enum('validity_type', ['unlimited', 'limited'])->default('unlimited');
            $table->integer('validity_value')->nullable();
            $table->enum('validity_unit', ['minutes', 'hours', 'days', 'months'])->nullable();
            $table->enum('limit_type', ['unlimited', 'limited'])->default('unlimited');
            $table->bigInteger('quota_bytes')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('shared_users')->default(1);
            $table->string('pool_name', 64)->nullable();
            $table->string('dns_servers', 128)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
