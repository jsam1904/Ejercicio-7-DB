<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    protected $fillable = [
        'team_id', 'first_name', 'last_name', 'position',
        'jersey_number', 'height_inches', 'weight_lbs',
        'date_of_birth', 'college', 'is_active',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'is_active'      => 'boolean',
        'height_inches'  => 'integer',
        'weight_lbs'     => 'integer',
        'jersey_number'  => 'integer',
    ];

    // Un jugador pertenece a un equipo (puede ser null si es agente libre)
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // Estadísticas individuales del jugador por partido
    public function playerGameStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }

    // Relación muchos-a-muchos: un jugador participa en muchos partidos y un partido tiene muchos jugadores.
    // La tabla player_game_stats actúa como pivote con datos adicionales de estadísticas.
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'player_game_stats')
            ->withPivot([
                'passing_yards', 'passing_touchdowns', 'interceptions_thrown',
                'rushing_yards', 'rushing_touchdowns',
                'receptions', 'receiving_yards', 'receiving_touchdowns',
                'tackles', 'sacks',
            ])
            ->withTimestamps();
    }

    // Historial de contratos del jugador con distintos equipos
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Contrato vigente actual
    public function activeContract(): HasOne
    {
        return $this->hasOne(Contract::class)->where('is_active', true)->latestOfMany();
    }

    // Historial de lesiones del jugador
    public function injuries(): HasMany
    {
        return $this->hasMany(Injury::class);
    }

    // Datos del draft (la mayoría de jugadores fue seleccionado en alguna ronda)
    public function draftPick(): HasOne
    {
        return $this->hasOne(DraftPick::class);
    }
}
