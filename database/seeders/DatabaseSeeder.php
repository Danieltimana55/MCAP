<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Primero crear los roles
        $this->call([
            RoleSeeder::class,
        ]);

        // Obtener los roles
        $adminRole = Role::where('name', Role::ADMINISTRADOR)->first();
        $employeeRole = Role::where('name', Role::EMPLEADO)->first();

        // Crear usuario administrador
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        // Crear usuario empleado (sin acceso)
        User::factory()->create([
            'name' => 'Empleado Test',
            'email' => 'empleado@example.com',
            'role_id' => $employeeRole->id,
        ]);

        $this->command->info('Usuarios de prueba creados:');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Empleado: empleado@example.com / password');
    }
}

