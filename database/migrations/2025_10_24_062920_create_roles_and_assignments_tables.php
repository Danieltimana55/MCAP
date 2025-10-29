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
        // Tabla de roles (Administrador, Empleado)
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'administrador', 'empleado'
            $table->string('display_name'); // 'Administrador', 'Empleado'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Tabla pivote: relación usuarios-roles
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        // Tabla de asignaciones/funciones para empleados por turno
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('function'); // 'recepcion', 'limpieza', 'cocina', etc.
            $table->string('display_name'); // 'Recepción', 'Limpieza', 'Cocina'
            $table->date('date'); // Fecha del turno
            $table->time('start_time')->nullable(); // Hora de inicio (opcional)
            $table->time('end_time')->nullable(); // Hora de fin (opcional)
            $table->text('notes')->nullable(); // Notas adicionales
            $table->boolean('is_active')->default(true); // Si está activo el turno
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['user_id', 'date']);
            $table->index(['function', 'date']);
        });

        // Agregar columna role_id a users (para el rol principal)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('assignments');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
