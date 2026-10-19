<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->unique(
                ['id_encuesta', 'id_usuario'],
                'envios_encuesta_usuario_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('envios', function (Blueprint $table) {
            $table->dropUnique('envios_encuesta_usuario_unique');
        });
    }
};