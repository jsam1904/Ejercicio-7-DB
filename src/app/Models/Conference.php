<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Conference extends Model
{
    protected $fillable = ['name', 'full_name'];

    // Una conferencia tiene muchas divisiones (AFC tiene 4: East, West, North, South)
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    // Atajo directo a todos los equipos sin pasar explícitamente por Division
    public function teams(): HasManyThrough
    {
        return $this->hasManyThrough(Team::class, Division::class);
    }
}
