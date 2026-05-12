<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Injury extends Model
{
    protected $fillable = [
        'player_id', 'injury_type', 'body_part', 'severity',
        'injury_date', 'return_date', 'games_missed',
    ];

    protected $casts = [
        'injury_date'  => 'date',
        'return_date'  => 'date',
        'games_missed' => 'integer',
    ];

    // Una lesión pertenece a un jugador
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
