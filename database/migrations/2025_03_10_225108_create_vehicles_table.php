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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('license_plate')->nullable();
            $table->string('model');
            $table->string('brand')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('chassis')->nullable();
            $table->string('device_id')->nullable();
            $table->string('sim_card')->nullable();
            $table->string('phone_number')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('traccar_id')->nullable()->unique()->comment('ID in Traccar system');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
