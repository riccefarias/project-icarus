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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('model');
            $table->string('brand');
            $table->enum('status', ['in_stock', 'with_technician', 'with_customer', 'defective', 'maintenance']);
            $table->string('imei')->nullable()->unique();
            $table->string('phone_number')->nullable();
            $table->string('chip_provider')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
