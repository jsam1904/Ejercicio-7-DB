<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coach extends Model
{
    protected $fillable = [
        'team_id', 'first_name', 'last_name', 'role', 'hire_date', 'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Un entrenador pertenece a un equipo
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
