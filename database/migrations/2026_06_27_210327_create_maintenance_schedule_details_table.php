<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('m_schedule_id')->notNull()->constrained('maintenance_schedules')->onDelete('cascade');
            $table->date('date'); 
            
            $table->string('observation')->nullable(); 
            $table->string('image')->nullable();       
            $table->boolean('status')->default(0);     
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedule_details');
    }
};