<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = ['year', 'start_date', 'end_date', 'champion_team_id'];

    protected $casts = [
        'year'       => 'integer',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // El equipo que ganó el Super Bowl de esa temporada
    public function champion(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'champion_team_id');
    }

    // Todos los partidos de la temporada (regular season + playoffs)
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
