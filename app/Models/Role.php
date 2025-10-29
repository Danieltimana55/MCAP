<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Constantes para roles
     */
    public const ADMINISTRADOR = 'administrador';
    public const EMPLEADO = 'empleado';

    /**
     * Relación muchos a muchos con usuarios
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Verificar si es rol de administrador
     */
    public function isAdmin(): bool
    {
        return $this->name === self::ADMINISTRADOR;
    }

    /**
     * Verificar si es rol de empleado
     */
    public function isEmployee(): bool
    {
        return $this->name === self::EMPLEADO;
    }
}