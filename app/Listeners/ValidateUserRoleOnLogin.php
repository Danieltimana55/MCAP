<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ValidateUserRoleOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected Request $request
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        // Verificar si el usuario tiene rol de administrador
        if (!$user->isAdmin()) {
            // Cerrar la sesión del usuario
            Auth::logout();
            
            // Invalidar la sesión
            $this->request->session()->invalidate();
            $this->request->session()->regenerateToken();

            // Lanzar excepción de validación con mensaje personalizado
            throw ValidationException::withMessages([
                'email' => 'No tiene acceso al sistema. Actualmente solo los administradores pueden ingresar.',
            ]);
        }
    }
}
