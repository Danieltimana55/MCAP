<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMINISTRADOR,
                'display_name' => 'Administrador',
                'description' => 'Usuario con acceso completo al sistema',
            ],
            [
                'name' => Role::EMPLEADO,
                'display_name' => 'Empleado',
                'description' => 'Usuario empleado con funciones asignables por turno',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('Roles creados exitosamente.');
    }
}

