<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftPick extends Model
{
    protected $fillable = [
        'player_id', 'team_id', 'draft_year',
        'round', 'pick_number', 'overall_pick',
    ];

    protected $casts = [
        'draft_year'   => 'integer',
        'round'        => 'integer',
        'pick_number'  => 'integer',
        'overall_pick' => 'integer',
    ];

    // La selección de draft corresponde a un jugador (hasOne inverso)
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    // El equipo que realizó la selección en el draft
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
