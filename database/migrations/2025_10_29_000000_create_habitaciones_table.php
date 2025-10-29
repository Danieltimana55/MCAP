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
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('tipo', ['sencilla', 'doble', 'suite']);
            $table->decimal('precio_base', 10, 2)->unsigned();
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
