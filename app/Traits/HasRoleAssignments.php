<?php

namespace App\Traits;

use App\Models\Assignment;
use Carbon\Carbon;

trait HasRoleAssignments
{
    /**
     * Asignar una función a un empleado para un día específico
     */
    public function assignFunction(
        string $function,
        string $displayName,
        string|Carbon $date,
        ?string $startTime = null,
        ?string $endTime = null,
        ?string $notes = null
    ): Assignment {
        return $this->assignments()->create([
            'function' => $function,
            'display_name' => $displayName,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'notes' => $notes,
            'is_active' => true,
        ]);
    }

    /**
     * Obtener la función asignada para hoy
     */
    public function getTodayFunction(): ?string
    {
        $assignment = $this->currentAssignment();
        return $assignment?->display_name;
    }

    /**
     * Verificar si tiene una función específica hoy
     */
    public function hasFunctionToday(string $function): bool
    {
        return $this->assignments()
            ->active()
            ->today()
            ->where('function', $function)
            ->exists();
    }

    /**
     * Obtener todas las funciones asignadas en un rango de fechas
     */
    public function getAssignmentsInRange(Carbon $startDate, Carbon $endDate)
    {
        return $this->assignments()
            ->active()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Desactivar una asignación
     */
    public function deactivateAssignment(int $assignmentId): bool
    {
        $assignment = $this->assignments()->find($assignmentId);
        if ($assignment) {
            return $assignment->update(['is_active' => false]);
        }
        return false;
    }
}
