# Validación de Roles en Login

## 📋 Implementación Completada

Se ha implementado la validación de roles durante el proceso de login. Ahora solo los usuarios con rol de **Administrador** pueden acceder al dashboard.

## 🔐 Funcionalidad

### ¿Qué hace?
- Cuando un usuario intenta iniciar sesión, el sistema valida su rol
- Si el usuario **ES administrador**: se le permite el acceso y es redirigido a `/dashboard`
- Si el usuario **NO ES administrador**: se cierra su sesión y ve un mensaje de error

### Mensaje de Error
Cuando un usuario sin permisos intenta acceder:
```
No tiene acceso al sistema. Actualmente solo los administradores pueden ingresar.
```

## 🗂️ Archivos Modificados/Creados

### 1. **Listener de Validación**
**Archivo**: `app/Listeners/ValidateUserRoleOnLogin.php`
- Escucha el evento `Login`
- Valida si el usuario es administrador usando `$user->isAdmin()`
- Si no es admin: cierra sesión y lanza una excepción de validación
- El mensaje de error aparece en el campo de email del formulario

### 2. **AppServiceProvider**
**Archivo**: `app/Providers/AppServiceProvider.php`
- Registra el listener `ValidateUserRoleOnLogin` para el evento `Login`
- Se ejecuta automáticamente después de cada login exitoso

### 3. **DatabaseSeeder**
**Archivo**: `database/seeders/DatabaseSeeder.php`
- Crea dos usuarios de prueba:
  - **Admin**: `admin@example.com` / `password` (✅ tiene acceso)
  - **Empleado**: `empleado@example.com` / `password` (❌ sin acceso)

## 🧪 Cómo Probar

### Prueba 1: Login con Administrador (✅ Debe funcionar)
1. Ve a `/login`
2. Ingresa:
   - Email: `admin@example.com`
   - Password: `password`
3. Click en "Log in"
4. **Resultado esperado**: Redirige a `/dashboard` exitosamente

### Prueba 2: Login con Empleado (❌ Debe fallar)
1. Ve a `/login`
2. Ingresa:
   - Email: `empleado@example.com`
   - Password: `password`
3. Click en "Log in"
4. **Resultado esperado**: 
   - NO inicia sesión
   - Muestra mensaje de error: "No tiene acceso al sistema. Actualmente solo los administradores pueden ingresar."
   - Permanece en la página de login

## 🔄 Flujo de Autenticación

```
Usuario ingresa credenciales
        ↓
Fortify valida credenciales
        ↓
¿Credenciales válidas? → NO → Muestra error de credenciales
        ↓ SÍ
Dispara evento Login
        ↓
ValidateUserRoleOnLogin escucha el evento
        ↓
¿Usuario es Admin?
        ↓                           ↓
       SÍ                          NO
        ↓                           ↓
Permite acceso          Cierra sesión + Error
        ↓                           ↓
Redirige a /dashboard    Regresa a /login
```

## 📝 Notas Técnicas

### Método `isAdmin()` en User Model
```php
public function isAdmin(): bool
{
    return $this->role?->name === Role::ADMINISTRADOR;
}
```
- Verifica si el rol del usuario es "administrador"
- Usa el operador seguro `?->` para evitar errores si no hay rol asignado

### Roles Definidos
En `app/Models/Role.php`:
```php
public const ADMINISTRADOR = 'administrador';
public const EMPLEADO = 'empleado';
```

### Middleware Actual del Dashboard
```php
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
```
- `auth`: Requiere autenticación
- `verified`: Requiere email verificado

## 🚀 Próximos Pasos (Futuro)

1. **Dashboard para Empleados**: Crear una vista diferente para usuarios con rol "empleado"
2. **Redirección Dinámica**: Redirigir a diferentes dashboards según el rol
3. **Más Roles**: Agregar roles adicionales según sea necesario
4. **Permisos Granulares**: Implementar sistema de permisos específicos

## ⚠️ Consideraciones

- Asegúrate de que todos los usuarios tengan un `role_id` asignado
- Los usuarios sin rol asignado (`role_id = null`) serán rechazados
- La validación ocurre **después** de la autenticación, por lo que las credenciales deben ser correctas

## 🔍 Debugging

Si necesitas verificar el rol de un usuario en la base de datos:
```sql
SELECT users.id, users.name, users.email, roles.name as role_name, roles.display_name
FROM users
LEFT JOIN roles ON users.role_id = roles.id;
```

## ✅ Checklist de Implementación

- [x] Crear listener `ValidateUserRoleOnLogin`
- [x] Registrar listener en `AppServiceProvider`
- [x] Actualizar `DatabaseSeeder` con usuarios de prueba
- [x] Ejecutar migraciones y seeders
- [ ] Probar login con admin (debe funcionar)
- [ ] Probar login con empleado (debe rechazar)
- [ ] Verificar mensaje de error en UI
