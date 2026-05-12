<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();

            // Estadísticas ofensivas
            $table->unsignedSmallInteger('passing_yards')->default(0);
            $table->unsignedTinyInteger('passing_touchdowns')->default(0);
            $table->unsignedTinyInteger('interceptions_thrown')->default(0);
            $table->smallInteger('rushing_yards')->default(0);  // signed: puede ser negativo
            $table->unsignedTinyInteger('rushing_touchdowns')->default(0);
            $table->unsignedTinyInteger('receptions')->default(0);
            $table->unsignedSmallInteger('receiving_yards')->default(0);
            $table->unsignedTinyInteger('receiving_touchdowns')->default(0);

            // Estadísticas defensivas
            $table->unsignedTinyInteger('tackles')->default(0);
            $table->decimal('sacks', 4, 1)->default(0.0); // e.g. 0.5, 1.0, 1.5

            // Un jugador solo puede tener un registro de estadísticas por partido
            $table->unique(['player_id', 'game_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_game_stats');
    }
};
