<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Assignment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'function',
        'display_name',
        'date',
        'start_time',
        'end_time',
        'notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relación con el usuario asignado
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Asignaciones activas
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Asignaciones de hoy
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope: Asignaciones por fecha
     */
    public function scopeForDate(Builder $query, string|Carbon $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope: Asignaciones por función
     */
    public function scopeByFunction(Builder $query, string $function): Builder
    {
        return $query->where('function', $function);
    }

    /**
     * Scope: Asignaciones futuras
     */
    public function scopeFuture(Builder $query): Builder
    {
        return $query->where('date', '>=', Carbon::today());
    }

    /**
     * Scope: Asignaciones pasadas
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('date', '<', Carbon::today());
    }
}