<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['conference_id', 'name'];

    // Cada división pertenece a una conferencia (AFC East → AFC)
    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }

    // Cada división agrupa 4 equipos
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
