# Sistema de Roles y Asignaciones

Este sistema implementa un modelo flexible de roles y asignaciones de turnos para empleados.

## Estructura

### 1. Roles Fijos
- **Administrador**: Acceso completo al sistema
- **Empleado**: Usuario con funciones asignables por turno

### 2. Asignaciones de Turnos
Los empleados pueden tener diferentes funciones asignadas por día. Por ejemplo:
- Recepción
- Limpieza
- Cocina
- Mantenimiento
- etc.

## Tablas de Base de Datos

### `roles`
- `id`: ID del rol
- `name`: Nombre interno (administrador, empleado)
- `display_name`: Nombre para mostrar
- `description`: Descripción del rol

### `role_user`
- Tabla pivote para relación muchos-a-muchos entre usuarios y roles

### `assignments`
- `id`: ID de la asignación
- `user_id`: ID del empleado asignado
- `function`: Nombre interno de la función (recepcion, limpieza, etc.)
- `display_name`: Nombre para mostrar de la función
- `date`: Fecha del turno
- `start_time`: Hora de inicio (opcional)
- `end_time`: Hora de fin (opcional)
- `notes`: Notas adicionales
- `is_active`: Si el turno está activo

## Instalación

1. Ejecutar las migraciones:
```bash
php artisan migrate
```

2. Ejecutar los seeders:
```bash
php artisan db:seed --class=RoleSeeder
```

O ejecutar todos los seeders:
```bash
php artisan db:seed
```

## Uso en Código

### Verificar rol de usuario

```php
// Verificar si es administrador
if ($user->isAdmin()) {
    // Acciones de administrador
}

// Verificar si es empleado
if ($user->isEmployee()) {
    // Acciones de empleado
}

// Verificar rol específico
if ($user->hasRole('administrador')) {
    // Acciones específicas
}
```

### Crear asignaciones de turno

```php
use App\Models\User;
use Carbon\Carbon;

// Obtener un empleado
$empleado = User::find(1);

// Asignar función para hoy
$empleado->assignments()->create([
    'function' => 'recepcion',
    'display_name' => 'Recepción',
    'date' => Carbon::today(),
    'start_time' => '08:00',
    'end_time' => '16:00',
    'notes' => 'Turno de mañana'
]);

// Asignar función para mañana
$empleado->assignments()->create([
    'function' => 'limpieza',
    'display_name' => 'Limpieza',
    'date' => Carbon::tomorrow(),
    'start_time' => '09:00',
    'end_time' => '17:00',
]);
```

### Consultar asignaciones

```php
use App\Models\Assignment;
use Carbon\Carbon;

// Obtener todas las asignaciones de hoy
$asignacionesHoy = Assignment::active()->today()->get();

// Obtener quién está en recepción hoy
$recepcionista = Assignment::active()
    ->today()
    ->byFunction('recepcion')
    ->first();

if ($recepcionista) {
    echo "Hoy en recepción: " . $recepcionista->user->name;
}

// Obtener la asignación actual de un empleado
$empleado = User::find(1);
$asignacionActual = $empleado->currentAssignment();

if ($asignacionActual) {
    echo "Función actual: " . $asignacionActual->display_name;
}

// Obtener asignaciones futuras de un empleado
$asignacionesFuturas = $empleado->futureAssignments()->get();
```

### Consultas avanzadas

```php
use App\Models\Assignment;
use Carbon\Carbon;

// Asignaciones de una semana
$inicioSemana = Carbon::now()->startOfWeek();
$finSemana = Carbon::now()->endOfWeek();

$asignacionesSemana = Assignment::active()
    ->whereBetween('date', [$inicioSemana, $finSemana])
    ->with('user')
    ->orderBy('date')
    ->get();

// Asignaciones por fecha específica
$fecha = Carbon::parse('2025-10-25');
$asignaciones = Assignment::active()
    ->forDate($fecha)
    ->get();

// Todas las asignaciones de un empleado
$empleado = User::find(1);
$todasAsignaciones = $empleado->assignments()
    ->orderBy('date', 'desc')
    ->get();
```

### Usar en Blade Templates

```php
// En tu controlador
public function dashboard()
{
    $user = auth()->user();
    
    return inertia('Dashboard', [
        'isAdmin' => $user->isAdmin(),
        'currentAssignment' => $user->currentAssignment(),
        'futureAssignments' => $user->futureAssignments()->get(),
    ]);
}
```

## Endpoints de API (Ejemplo)

Puedes agregar estas rutas a `routes/web.php` o `routes/api.php`:

```php
use App\Http\Controllers\AssignmentController;

// Asignaciones
Route::prefix('assignments')->group(function () {
    Route::get('/today', [AssignmentController::class, 'today']);
    Route::get('/by-date', [AssignmentController::class, 'byDate']);
    Route::post('/', [AssignmentController::class, 'store']);
    Route::put('/{assignment}', [AssignmentController::class, 'update']);
    Route::delete('/{assignment}', [AssignmentController::class, 'destroy']);
    Route::get('/who-is-on', [AssignmentController::class, 'whoIsOnFunction']);
    Route::get('/employee/{user}', [AssignmentController::class, 'employeeSchedule']);
    Route::get('/calendar', [AssignmentController::class, 'monthlyCalendar']);
});
```

## Ejemplos Prácticos

### Ejemplo 1: Rotar empleados en recepción

```php
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

// Obtener todos los empleados
$empleados = User::whereHas('role', function($query) {
    $query->where('name', Role::EMPLEADO);
})->get();

// Asignar recepción rotativamente (lunes a viernes)
$fecha = Carbon::today();
$indexEmpleado = 0;

for ($i = 0; $i < 5; $i++) { // 5 días laborables
    if ($fecha->isWeekday()) {
        $empleado = $empleados[$indexEmpleado % $empleados->count()];
        
        $empleado->assignments()->create([
            'function' => 'recepcion',
            'display_name' => 'Recepción',
            'date' => $fecha->copy(),
            'start_time' => '08:00',
            'end_time' => '16:00',
        ]);
        
        $indexEmpleado++;
    }
    $fecha->addDay();
}
```

### Ejemplo 2: Ver quién está haciendo qué hoy

```php
use App\Models\Assignment;

$asignacionesHoy = Assignment::with('user')
    ->active()
    ->today()
    ->get();

foreach ($asignacionesHoy as $asignacion) {
    echo "{$asignacion->user->name} está en {$asignacion->display_name}\n";
}
```

### Ejemplo 3: Middleware para verificar función

```php
// app/Http/Middleware/CheckEmployeeFunction.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEmployeeFunction
{
    public function handle(Request $request, Closure $next, string $function)
    {
        $user = $request->user();
        
        if (!$user->isEmployee()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $hasFunction = $user->assignments()
            ->active()
            ->today()
            ->where('function', $function)
            ->exists();
            
        if (!$hasFunction) {
            return response()->json([
                'error' => 'No tienes esta función asignada hoy'
            ], 403);
        }
        
        return $next($request);
    }
}

// Uso en rutas:
// Route::post('/check-in', [ReceptionController::class, 'checkIn'])
//     ->middleware('check.function:recepcion');
```

## Buenas Prácticas

1. **Siempre usa los scopes** para consultas comunes:
   - `active()` - Solo asignaciones activas
   - `today()` - Asignaciones de hoy
   - `future()` - Asignaciones futuras
   - `byFunction()` - Filtrar por función

2. **Usa eager loading** para evitar el problema N+1:
   ```php
   Assignment::with('user')->get();
   ```

3. **Valida las asignaciones** antes de crearlas para evitar conflictos:
   ```php
   // Verificar que el empleado no tenga otra asignación a la misma hora
   $conflicto = Assignment::where('user_id', $userId)
       ->where('date', $fecha)
       ->active()
       ->exists();
   ```

4. **Usa transacciones** para operaciones múltiples:
   ```php
   DB::transaction(function () use ($empleados, $fecha) {
       foreach ($empleados as $empleado) {
           $empleado->assignments()->create([...]);
       }
   });
   ```
