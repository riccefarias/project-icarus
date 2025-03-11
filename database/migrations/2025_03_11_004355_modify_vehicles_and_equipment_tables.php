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
        // Modificar a tabela de veículos para remover campos relacionados a equipamento
        Schema::table('vehicles', function (Blueprint $table) {
            // Crie uma coluna equipment_id que posteriormente será preenchida
            $table->foreignId('equipment_id')->nullable()->after('chassis');

            // Mova dados do device_id, sim_card, phone_number para a tabela equipment
            // Na tabela de equipamentos, o device_id é o serial_number

            // Depois remova as colunas
            $table->dropColumn([
                'device_id',
                'sim_card',
                'phone_number',
            ]);
        });

        // Adicionar coluna traccar_id na tabela equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('traccar_id')->nullable()->after('imei');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar as colunas removidas na tabela vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('chassis');
            $table->string('sim_card')->nullable()->after('device_id');
            $table->string('phone_number')->nullable()->after('sim_card');

            $table->dropColumn('equipment_id');
        });

        // Remover a coluna adicionada na tabela equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('traccar_id');
        });
    }
};
