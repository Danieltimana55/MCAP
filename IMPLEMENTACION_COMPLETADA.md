# 🎯 Sistema de Roles y Asignaciones - Resumen de Implementación

## ✅ Lo que se ha implementado

### 📊 Estructura de Base de Datos

1. **Tabla `roles`**
   - Dos roles fijos: Administrador y Empleado
   - Campos: id, name, display_name, description

2. **Tabla `role_user`** (pivote)
   - Relación muchos-a-muchos entre usuarios y roles
   - Campos: id, user_id, role_id

3. **Tabla `assignments`** (asignaciones/turnos)
   - Gestiona qué empleado hace qué función cada día
   - Campos: id, user_id, function, display_name, date, start_time, end_time, notes, is_active
   - **Ejemplo**: María hace recepción hoy, mañana Ana hace limpieza, etc.

4. **Campo `role_id`** agregado a la tabla `users`
   - Rol principal del usuario

### 🏗️ Modelos Creados

1. **`Role`** (app/Models/Role.php)
   - Constantes: `ADMINISTRADOR`, `EMPLEADO`
   - Métodos: `isAdmin()`, `isEmployee()`
   - Relación con usuarios

2. **`Assignment`** (app/Models/Assignment.php)
   - Gestiona las asignaciones de funciones por turno
   - Scopes útiles: `active()`, `today()`, `future()`, `past()`, `forDate()`, `byFunction()`
   - Relación con usuario

3. **`User`** (app/Models/User.php) - ACTUALIZADO
   - Métodos agregados:
     - `isAdmin()`, `isEmployee()`, `hasRole()`
     - `currentAssignment()` - Obtiene la asignación de hoy
     - `futureAssignments()` - Obtiene asignaciones futuras
   - Relaciones: `role()`, `roles()`, `assignments()`
   - Trait: `HasRoleAssignments`

### 🛠️ Herramientas Adicionales

1. **`HasRoleAssignments`** (app/Traits/HasRoleAssignments.php)
   - Trait con métodos útiles para gestionar asignaciones
   - `assignFunction()`, `getTodayFunction()`, `hasFunctionToday()`, etc.

2. **`AssignmentController`** (app/Http/Controllers/AssignmentController.php)
   - CRUD completo de asignaciones
   - Endpoints especiales:
     - `/assignments/today` - Ver asignaciones de hoy
     - `/assignments/who-is-on` - Ver quién está en una función específica
     - `/employee/{user}/schedule` - Ver horario de un empleado
     - `/calendar` - Ver calendario mensual

3. **`CheckRole`** (app/Http/Middleware/CheckRole.php)
   - Middleware para proteger rutas por rol
   - Uso: `->middleware('role:administrador')`

4. **`RoleSeeder`** (database/seeders/RoleSeeder.php)
   - Crea los roles Administrador y Empleado

5. **`ExampleAssignmentSeeder`** (database/seeders/ExampleAssignmentSeeder.php)
   - Crea usuarios y asignaciones de ejemplo para testing

### 📄 Documentación

1. **`ROLES_Y_ASIGNACIONES.md`**
   - Documentación completa del sistema
   - Ejemplos de uso
   - Buenas prácticas

2. **`routes/roles-example.php`**
   - Ejemplos de rutas para el sistema

## 🚀 Cómo Usar

### 1. Migraciones (YA EJECUTADAS ✅)
```bash
php artisan migrate
```

### 2. Crear Roles (YA EJECUTADO ✅)
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. (OPCIONAL) Crear Datos de Ejemplo
```bash
php artisan db:seed --class=ExampleAssignmentSeeder
```
Esto creará:
- 1 administrador (admin@mcap.com / password)
- 3 empleadas (maria@mcap.com, ana@mcap.com, carmen@mcap.com / password)
- Asignaciones de ejemplo para los próximos 7 días

## 💡 Casos de Uso Comunes

### Verificar el rol de un usuario
```php
if (auth()->user()->isAdmin()) {
    // Es administrador
}

if (auth()->user()->isEmployee()) {
    // Es empleado
}
```

### Ver qué función tiene un empleado hoy
```php
$empleado = User::find(1);
$asignacion = $empleado->currentAssignment();

if ($asignacion) {
    echo "Hoy hace: " . $asignacion->display_name;
}
```

### Asignar una función a un empleado
```php
use Carbon\Carbon;

$empleado = User::find(1);
$empleado->assignments()->create([
    'function' => 'recepcion',
    'display_name' => 'Recepción',
    'date' => Carbon::today(),
    'start_time' => '08:00',
    'end_time' => '16:00',
    'notes' => 'Turno de mañana'
]);
```

### Ver quién está en recepción hoy
```php
use App\Models\Assignment;

$recepcionista = Assignment::active()
    ->today()
    ->byFunction('recepcion')
    ->with('user')
    ->first();

if ($recepcionista) {
    echo $recepcionista->user->name . " está en recepción hoy";
}
```

### Rotar empleados en diferentes funciones
```php
$empleados = User::whereHas('role', function($query) {
    $query->where('name', Role::EMPLEADO);
})->get();

$funciones = ['recepcion', 'limpieza', 'cocina'];
$fecha = Carbon::today();

// Asignar funciones rotativamente
foreach ($empleados as $index => $empleado) {
    $funcion = $funciones[$index % count($funciones)];
    
    $empleado->assignments()->create([
        'function' => $funcion,
        'display_name' => ucfirst($funcion),
        'date' => $fecha,
        'is_active' => true,
    ]);
}
```

## 🔒 Proteger Rutas por Rol

### Registrar el middleware en bootstrap/app.php:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### Usar en rutas:
```php
// Solo administradores
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/admin', ...);
});

// Solo empleados
Route::middleware(['auth', 'role:empleado'])->group(function () {
    Route::get('/employee', ...);
});
```

## 🎨 Estructura del Sistema

```
Usuario
  ├── role_id (rol principal)
  ├── role() → Relación con Role
  ├── roles() → Relación muchos-a-muchos con Roles
  └── assignments() → Relación con Assignments

Role
  ├── ADMINISTRADOR (constante)
  ├── EMPLEADO (constante)
  └── users() → Relación con Users

Assignment (Asignación/Turno)
  ├── user_id (empleado asignado)
  ├── function (nombre interno: 'recepcion', 'limpieza')
  ├── display_name (nombre para mostrar: 'Recepción', 'Limpieza')
  ├── date (fecha del turno)
  ├── start_time, end_time (horario)
  └── is_active (si está activo)
```

## 🎯 Ventajas de esta Implementación

1. ✅ **Flexible**: Los empleados pueden rotar entre diferentes funciones
2. ✅ **Escalable**: Fácil agregar nuevas funciones sin cambiar la estructura
3. ✅ **Auditable**: Todas las asignaciones quedan registradas con fechas
4. ✅ **Consultas eficientes**: Scopes optimizados para las consultas más comunes
5. ✅ **Buenas prácticas**: Sigue las convenciones de Laravel
6. ✅ **Type-safe**: Usa constantes para evitar errores de tipeo

## 📋 Próximos Pasos Sugeridos

1. Registrar el middleware `CheckRole` en `bootstrap/app.php`
2. Agregar las rutas necesarias desde `routes/roles-example.php`
3. Crear las vistas/componentes de Inertia para gestionar asignaciones
4. Implementar notificaciones cuando se asignan funciones
5. Crear un calendario visual para ver todas las asignaciones

## 🆘 Soporte

Toda la documentación detallada está en: `ROLES_Y_ASIGNACIONES.md`

Ejemplos de rutas en: `routes/roles-example.php`
