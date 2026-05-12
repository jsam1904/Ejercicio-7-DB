<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stadium extends Model
{
    protected $fillable = [
        'name', 'city', 'state', 'capacity',
        'surface', 'roof_type', 'year_opened',
    ];

    protected $casts = [
        'capacity'    => 'integer',
        'year_opened' => 'integer',
    ];

    // Un estadio puede ser la sede de uno o más equipos (e.g. MetLife: Giants y Jets)
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    // Todos los partidos disputados en este estadio
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
