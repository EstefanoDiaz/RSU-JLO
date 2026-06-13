<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('conductor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assistant1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assistant2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('work_days'); // ["lun","mar","mie","jue","vie","sab","dom"]
            $table->enum('status', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_groups');
    }
};