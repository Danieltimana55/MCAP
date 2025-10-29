<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExampleAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder crea usuarios de ejemplo y asignaciones para demostrar el sistema
     */
    public function run(): void
    {
        // Obtener los roles
        $roleAdmin = Role::where('name', Role::ADMINISTRADOR)->first();
        $roleEmpleado = Role::where('name', Role::EMPLEADO)->first();

        // Crear un administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@mcap.com'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('password'),
                'role_id' => $roleAdmin->id,
            ]
        );

        // Crear empleados de ejemplo
        $empleados = [
            [
                'name' => 'María González',
                'email' => 'maria@mcap.com',
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana@mcap.com',
            ],
            [
                'name' => 'Carmen López',
                'email' => 'carmen@mcap.com',
            ],
        ];

        $empleadosCreados = [];
        foreach ($empleados as $empleadoData) {
            $empleado = User::firstOrCreate(
                ['email' => $empleadoData['email']],
                [
                    'name' => $empleadoData['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $roleEmpleado->id,
                ]
            );
            $empleadosCreados[] = $empleado;
        }

        // Crear asignaciones de ejemplo para la próxima semana
        $funciones = [
            'recepcion' => 'Recepción',
            'limpieza' => 'Limpieza',
            'cocina' => 'Cocina',
        ];

        $fecha = Carbon::today();
        $empleadoIndex = 0;

        // Asignar funciones para los próximos 7 días
        for ($i = 0; $i < 7; $i++) {
            foreach ($funciones as $funcion => $displayName) {
                $empleado = $empleadosCreados[$empleadoIndex % count($empleadosCreados)];
                
                Assignment::create([
                    'user_id' => $empleado->id,
                    'function' => $funcion,
                    'display_name' => $displayName,
                    'date' => $fecha->copy()->addDays($i),
                    'start_time' => '08:00',
                    'end_time' => '16:00',
                    'notes' => 'Turno de ' . strtolower($displayName),
                    'is_active' => true,
                ]);

                $empleadoIndex++;
            }
        }

        $this->command->info('✅ Usuarios y asignaciones de ejemplo creados exitosamente.');
        $this->command->info('📧 Email admin: admin@mcap.com | Password: password');
        $this->command->info('📧 Email empleados: maria@mcap.com, ana@mcap.com, carmen@mcap.com | Password: password');
    }
}

