<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'player_id', 'team_id', 'start_year', 'end_year',
        'total_value', 'annual_salary', 'signing_bonus', 'is_active',
    ];

    protected $casts = [
        'start_year'    => 'integer',
        'end_year'      => 'integer',
        'total_value'   => 'integer',
        'annual_salary' => 'integer',
        'signing_bonus' => 'integer',
        'is_active'     => 'boolean',
    ];

    // El contrato corresponde a un jugador
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    // El contrato fue firmado con un equipo específico
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
