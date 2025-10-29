<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasRoleAssignments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoleAssignments;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Relación con el rol principal
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación muchos a muchos con roles (si necesitas roles adicionales)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Relación con asignaciones/turnos
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMINISTRADOR;
    }

    /**
     * Verificar si el usuario es empleado
     */
    public function isEmployee(): bool
    {
        return $this->role?->name === Role::EMPLEADO;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName 
            || $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Obtener la asignación actual (de hoy)
     */
    public function currentAssignment(): ?Assignment
    {
        return $this->assignments()
            ->active()
            ->today()
            ->first();
    }

    /**
     * Obtener asignaciones futuras
     */
    public function futureAssignments()
    {
        return $this->assignments()
            ->active()
            ->future()
            ->orderBy('date');
    }
}
