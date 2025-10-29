<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Nombre del rol requerido (administrador, empleado)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder.');
        }

        // Verificar si el usuario tiene el rol requerido
        if (!$user->hasRole($role)) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
