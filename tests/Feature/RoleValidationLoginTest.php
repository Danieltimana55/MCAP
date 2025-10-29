<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('usuario administrador puede hacer login y acceder al dashboard', function () {
    // Crear roles
    $adminRole = Role::create([
        'name' => Role::ADMINISTRADOR,
        'display_name' => 'Administrador',
        'description' => 'Usuario con acceso completo',
    ]);

    // Crear usuario administrador
    $admin = User::factory()->create([
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'role_id' => $adminRole->id,
    ]);

    // Intentar hacer login
    $response = $this->post('/login', [
        'email' => 'admin@test.com',
        'password' => 'password',
    ]);

    // Verificar que fue redirigido al dashboard
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($admin);
});

test('usuario empleado no puede hacer login', function () {
    // Crear roles
    $employeeRole = Role::create([
        'name' => Role::EMPLEADO,
        'display_name' => 'Empleado',
        'description' => 'Usuario empleado',
    ]);

    // Crear usuario empleado
    $employee = User::factory()->create([
        'email' => 'empleado@test.com',
        'password' => Hash::make('password'),
        'role_id' => $employeeRole->id,
    ]);

    // Intentar hacer login
    $response = $this->post('/login', [
        'email' => 'empleado@test.com',
        'password' => 'password',
    ]);

    // Verificar que NO está autenticado
    $this->assertGuest();
    
    // Verificar que se redirigió al login
    $response->assertRedirect('/login');
    
    // Verificar que hay errores de validación
    $response->assertSessionHasErrors(['email']);
});

test('usuario sin rol no puede hacer login', function () {
    // Crear usuario sin rol
    $user = User::factory()->create([
        'email' => 'sinrol@test.com',
        'password' => Hash::make('password'),
        'role_id' => null,
    ]);

    // Intentar hacer login
    $response = $this->post('/login', [
        'email' => 'sinrol@test.com',
        'password' => 'password',
    ]);

    // Verificar que NO está autenticado
    $this->assertGuest();
    
    // Verificar que se redirigió al login
    $response->assertRedirect('/login');
    
    // Verificar que hay errores de validación
    $response->assertSessionHasErrors(['email']);
});

test('mensaje de error es claro para usuarios sin acceso', function () {
    // Crear rol empleado
    $employeeRole = Role::create([
        'name' => Role::EMPLEADO,
        'display_name' => 'Empleado',
        'description' => 'Usuario empleado',
    ]);

    // Crear usuario empleado
    User::factory()->create([
        'email' => 'empleado@test.com',
        'password' => Hash::make('password'),
        'role_id' => $employeeRole->id,
    ]);

    // Intentar hacer login
    $response = $this->post('/login', [
        'email' => 'empleado@test.com',
        'password' => 'password',
    ]);

    // Verificar el mensaje de error específico
    $response->assertSessionHasErrors([
        'email' => 'No tiene acceso al sistema. Actualmente solo los administradores pueden ingresar.'
    ]);
});
