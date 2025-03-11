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
        // Remover a coluna vehicle_id da tabela equipment, já que a relação correta é:
        // Um equipamento pertence a um veículo (não o contrário)
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
        });

        // Garantir que a chave estrangeira na tabela vehicles está corretamente configurada
        Schema::table('vehicles', function (Blueprint $table) {
            // Verificar se já existe, se existir, remover e recriar com as constraints corretas
            if (Schema::hasColumn('vehicles', 'equipment_id')) {
                $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar a coluna vehicle_id na tabela equipment
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
        });

        // Remover a restrição foreign key adicionada
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
        });
    }
};
