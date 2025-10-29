<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignEmployeeFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:assign 
                            {user? : ID o email del empleado}
                            {function? : Función a asignar}
                            {--date= : Fecha de la asignación (Y-m-d)}
                            {--start= : Hora de inicio (H:i)}
                            {--end= : Hora de fin (H:i)}
                            {--notes= : Notas adicionales}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar una función a un empleado para un turno específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 Asignación de Funciones a Empleados');
        $this->newLine();

        // Obtener o seleccionar empleado
        $user = $this->getEmployee();
        if (!$user) {
            $this->error('❌ No se pudo obtener el empleado.');
            return Command::FAILURE;
        }

        // Obtener o seleccionar función
        $functions = [
            'recepcion' => 'Recepción',
            'limpieza' => 'Limpieza',
            'cocina' => 'Cocina',
            'mantenimiento' => 'Mantenimiento',
            'administracion' => 'Administración',
        ];

        $functionKey = $this->argument('function');
        
        if (!$functionKey || !isset($functions[$functionKey])) {
            $functionKey = $this->choice(
                '¿Qué función deseas asignar?',
                array_keys($functions)
            );
        }

        $displayName = $functions[$functionKey];

        // Obtener fecha
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : $this->askForDate();

        // Obtener horarios
        $startTime = $this->option('start') ?: $this->ask('Hora de inicio (opcional, formato HH:MM)', '08:00');
        $endTime = $this->option('end') ?: $this->ask('Hora de fin (opcional, formato HH:MM)', '16:00');
        $notes = $this->option('notes') ?: $this->ask('Notas adicionales (opcional)');

        // Verificar conflictos
        $existingAssignment = Assignment::where('user_id', $user->id)
            ->where('date', $date)
            ->active()
            ->first();

        if ($existingAssignment) {
            if (!$this->confirm("⚠️  {$user->name} ya tiene asignada '{$existingAssignment->display_name}' para {$date->format('d/m/Y')}. ¿Desactivar y crear nueva?")) {
                $this->warn('Operación cancelada.');
                return Command::SUCCESS;
            }
            $existingAssignment->update(['is_active' => false]);
        }

        // Crear la asignación
        $assignment = Assignment::create([
            'user_id' => $user->id,
            'function' => $functionKey,
            'display_name' => $displayName,
            'date' => $date,
            'start_time' => $startTime ?: null,
            'end_time' => $endTime ?: null,
            'notes' => $notes,
            'is_active' => true,
        ]);

        $this->newLine();
        $this->info('✅ Asignación creada exitosamente:');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Empleado', $user->name],
                ['Función', $displayName],
                ['Fecha', $date->format('d/m/Y')],
                ['Horario', ($startTime && $endTime) ? "$startTime - $endTime" : 'No especificado'],
                ['Notas', $notes ?: 'Sin notas'],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Obtener el empleado
     */
    private function getEmployee(): ?User
    {
        $userInput = $this->argument('user');

        if ($userInput) {
            // Buscar por ID o email
            $user = is_numeric($userInput)
                ? User::find($userInput)
                : User::where('email', $userInput)->first();

            if ($user && $user->isEmployee()) {
                return $user;
            }
        }

        // Listar empleados para seleccionar
        $employees = User::whereHas('role', function ($query) {
            $query->where('name', Role::EMPLEADO);
        })->get();

        if ($employees->isEmpty()) {
            $this->error('No hay empleados registrados.');
            return null;
        }

        $choices = $employees->pluck('name', 'id')->toArray();
        $userId = $this->choice('Selecciona un empleado:', $choices);
        
        return User::find($userId);
    }

    /**
     * Preguntar por la fecha
     */
    private function askForDate(): Carbon
    {
        $dateOptions = [
            'today' => 'Hoy (' . Carbon::today()->format('d/m/Y') . ')',
            'tomorrow' => 'Mañana (' . Carbon::tomorrow()->format('d/m/Y') . ')',
            'custom' => 'Fecha personalizada',
        ];

        $choice = $this->choice('¿Para qué fecha?', $dateOptions);

        switch ($choice) {
            case 'today':
                return Carbon::today();
            case 'tomorrow':
                return Carbon::tomorrow();
            case 'custom':
                $dateInput = $this->ask('Ingresa la fecha (formato: YYYY-MM-DD o DD/MM/YYYY)');
                try {
                    return Carbon::parse($dateInput);
                } catch (\Exception $e) {
                    $this->error('Fecha inválida. Usando hoy.');
                    return Carbon::today();
                }
            default:
                return Carbon::today();
        }
    }
}
