<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programacion_cambios', function (Blueprint $table) {
            $table->unsignedBigInteger('valor_anterior_id')->nullable()->after('valor_anterior');
            $table->unsignedBigInteger('valor_nuevo_id')->nullable()->after('valor_nuevo');
        });
    }

    public function down(): void
    {
        Schema::table('programacion_cambios', function (Blueprint $table) {
            $table->dropColumn(['valor_anterior_id', 'valor_nuevo_id']);
        });
    }
};