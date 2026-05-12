<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGameStat extends Model
{
    protected $fillable = [
        'player_id', 'game_id',
        'passing_yards', 'passing_touchdowns', 'interceptions_thrown',
        'rushing_yards', 'rushing_touchdowns',
        'receptions', 'receiving_yards', 'receiving_touchdowns',
        'tackles', 'sacks',
    ];

    protected $casts = [
        'passing_yards'         => 'integer',
        'passing_touchdowns'    => 'integer',
        'interceptions_thrown'  => 'integer',
        'rushing_yards'         => 'integer',
        'rushing_touchdowns'    => 'integer',
        'receptions'            => 'integer',
        'receiving_yards'       => 'integer',
        'receiving_touchdowns'  => 'integer',
        'tackles'               => 'integer',
        'sacks'                 => 'decimal:1',
    ];

    // Las estadísticas pertenecen a un jugador
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    // Las estadísticas corresponden a un partido específico
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
