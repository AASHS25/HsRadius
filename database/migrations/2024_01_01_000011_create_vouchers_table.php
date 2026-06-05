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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->enum('status', ['unused', 'used', 'expired'])->default('unused');
            $table->string('batch_name', 64)->nullable();
            $table->string('used_by', 64)->nullable();
            $table->dateTime('used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('validity_value')->nullable();
            $table->enum('validity_unit', ['minutes', 'hours', 'days', 'months'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
