<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['preguntas', 'envios'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['id_encuesta']);
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('id_encuesta')->references('id')->on('encuestas');
            });
        }
    }

    public function down(): void
    {
        // Keep the corrected foreign keys; reverting would point to a nonexistent table.
    }
};