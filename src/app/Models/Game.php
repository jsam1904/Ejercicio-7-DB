<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'season_id', 'week', 'home_team_id', 'away_team_id', 'stadium_id',
        'home_score', 'away_score', 'game_date', 'is_playoff',
    ];

    protected $casts = [
        'game_date'  => 'datetime',
        'is_playoff' => 'boolean',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'week'       => 'integer',
    ];

    // El partido pertenece a una temporada
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    // Equipo local
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    // Equipo visitante
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    // El estadio donde se jugó el partido
    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    // Registros individuales de estadísticas de cada jugador en este partido
    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }

    // Relación muchos-a-muchos: un partido involucra a muchos jugadores y un jugador juega en muchos partidos.
    // La tabla player_game_stats es el pivote con estadísticas detalladas.
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_game_stats')
            ->withPivot([
                'passing_yards', 'passing_touchdowns', 'interceptions_thrown',
                'rushing_yards', 'rushing_touchdowns',
                'receptions', 'receiving_yards', 'receiving_touchdowns',
                'tackles', 'sacks',
            ])
            ->withTimestamps();
    }
}
