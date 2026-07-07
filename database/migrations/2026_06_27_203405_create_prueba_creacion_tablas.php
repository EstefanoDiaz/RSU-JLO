<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });

        Schema::create('mantenimiento_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('responsable_id')->constrained('users')->restrictOnDelete();
            $table->enum('tipo', ['Preventivo', 'Limpieza', 'Reparación']);
            $table->enum('dia_semana', ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });

        Schema::create('mantenimiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('mantenimiento_horarios')->cascadeOnDelete();
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->string('imagen')->nullable(); // path en storage
            $table->boolean('realizado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_detalles');
        Schema::dropIfExists('mantenimiento_horarios');
        Schema::dropIfExists('mantenimientos');
    }
};