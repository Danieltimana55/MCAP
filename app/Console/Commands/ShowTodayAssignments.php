<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ShowTodayAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:today 
                            {--date= : Mostrar asignaciones de una fecha específica (Y-m-d)}
                            {--function= : Filtrar por función específica}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mostrar las asignaciones de funciones para hoy (o una fecha específica)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $function = $this->option('function');

        $this->info("📋 Asignaciones para: " . $date->format('l, d/m/Y'));
        $this->newLine();

        // Construir la consulta
        $query = Assignment::with('user')
            ->active()
            ->forDate($date)
            ->orderBy('function');

        if ($function) {
            $query->where('function', $function);
        }

        $assignments = $query->get();

        if ($assignments->isEmpty()) {
            $this->warn('⚠️  No hay asignaciones para esta fecha.');
            return Command::SUCCESS;
        }

        // Preparar datos para la tabla
        $tableData = $assignments->map(function ($assignment) {
            $schedule = '';
            if ($assignment->start_time && $assignment->end_time) {
                $schedule = $assignment->start_time->format('H:i') . ' - ' . $assignment->end_time->format('H:i');
            }

            return [
                'Empleado' => $assignment->user->name,
                'Función' => $assignment->display_name,
                'Horario' => $schedule ?: 'No especificado',
                'Notas' => Str::limit($assignment->notes ?? '-', 30),
            ];
        })->toArray();

        $this->table(
            ['Empleado', 'Función', 'Horario', 'Notas'],
            $tableData
        );

        $this->newLine();
        $this->info("✅ Total: " . $assignments->count() . " asignación(es)");

        return Command::SUCCESS;
    }
}
