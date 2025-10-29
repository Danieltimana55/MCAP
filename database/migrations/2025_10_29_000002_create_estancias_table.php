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
        Schema::create('estancias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->restrictOnDelete();
            $table->foreignId('huesped_id')->constrained('huespedes')->restrictOnDelete();
            $table->dateTime('fecha_hora_entrada');
            $table->dateTime('fecha_hora_salida')->nullable();
            $table->enum('estado_estancia', ['activa', 'finalizada', 'cancelada']);
            $table->decimal('total_pagado', 10, 2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estancias');
    }
};
