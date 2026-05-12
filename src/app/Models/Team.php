<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'division_id', 'stadium_id', 'name', 'city',
        'abbreviation', 'primary_color', 'secondary_color', 'founded_year',
    ];

    protected $casts = [
        'founded_year' => 'integer',
    ];

    // Un equipo pertenece a una división (Chiefs → AFC West)
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    // Un equipo juega en un estadio
    public function stadium(): BelongsTo
    {
        return $this->belongsTo(Stadium::class);
    }

    // Un equipo tiene muchos jugadores en su roster
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    // Un equipo tiene múltiples entrenadores (head coach, coordinadores, etc.)
    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }

    // Partidos jugados como local
    public function homeGames(): HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    // Partidos jugados como visitante
    public function awayGames(): HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    // Contratos firmados con este equipo
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Temporadas en las que este equipo fue campeón del Super Bowl
    public function championSeasons(): HasMany
    {
        return $this->hasMany(Season::class, 'champion_team_id');
    }

    // Selecciones de draft realizadas por este equipo
    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }
}
