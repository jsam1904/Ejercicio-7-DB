<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete(); // equipo que seleccionó al jugador
            $table->unsignedSmallInteger('draft_year');
            $table->unsignedTinyInteger('round');         // 1-7
            $table->unsignedTinyInteger('pick_number');   // posición dentro de la ronda (1-32)
            $table->unsignedSmallInteger('overall_pick'); // posición global del draft
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_picks');
    }
};
