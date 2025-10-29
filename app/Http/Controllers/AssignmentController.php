<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Controlador de ejemplo para gestionar asignaciones de turnos
 */
class AssignmentController extends Controller
{
    /**
     * Obtener todas las asignaciones de hoy
     */
    public function today()
    {
        $assignments = Assignment::with('user')
            ->active()
            ->today()
            ->get();

        return response()->json($assignments);
    }

    /**
     * Obtener asignaciones por fecha
     */
    public function byDate(Request $request)
    {
        $date = $request->input('date', Carbon::today());
        
        $assignments = Assignment::with('user')
            ->active()
            ->forDate($date)
            ->get();

        return response()->json($assignments);
    }

    /**
     * Crear una nueva asignación
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'function' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $assignment = Assignment::create($validated);

        return response()->json([
            'message' => 'Asignación creada exitosamente',
            'assignment' => $assignment->load('user')
        ], 201);
    }

    /**
     * Actualizar una asignación existente
     */
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'function' => 'sometimes|string|max:255',
            'display_name' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $assignment->update($validated);

        return response()->json([
            'message' => 'Asignación actualizada exitosamente',
            'assignment' => $assignment->load('user')
        ]);
    }

    /**
     * Eliminar una asignación
     */
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return response()->json([
            'message' => 'Asignación eliminada exitosamente'
        ]);
    }

    /**
     * Obtener el empleado asignado a una función específica hoy
     */
    public function whoIsOnFunction(Request $request)
    {
        $function = $request->input('function');
        
        $assignment = Assignment::with('user')
            ->active()
            ->today()
            ->byFunction($function)
            ->first();

        if (!$assignment) {
            return response()->json([
                'message' => 'No hay empleado asignado a esta función hoy'
            ], 404);
        }

        return response()->json([
            'function' => $assignment->display_name,
            'employee' => $assignment->user->name,
            'assignment' => $assignment
        ]);
    }

    /**
     * Obtener todas las asignaciones futuras de un empleado
     */
    public function employeeSchedule(User $user)
    {
        $assignments = $user->assignments()
            ->active()
            ->future()
            ->orderBy('date')
            ->get();

        return response()->json([
            'employee' => $user->name,
            'assignments' => $assignments
        ]);
    }

    /**
     * Obtener el calendario de asignaciones del mes
     */
    public function monthlyCalendar(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $assignments = Assignment::with('user')
            ->active()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('function')
            ->get()
            ->groupBy('date');

        return response()->json([
            'month' => $month,
            'year' => $year,
            'assignments' => $assignments
        ]);
    }
}
