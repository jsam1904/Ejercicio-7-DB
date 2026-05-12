<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('injuries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('injury_type', 100); // "ACL Tear", "Hamstring Strain", etc.
            $table->string('body_part', 50);    // "knee", "shoulder", "ankle", etc.
            $table->enum('severity', ['minor', 'moderate', 'severe']);
            $table->date('injury_date');
            $table->date('return_date')->nullable(); // NULL = aún en lista de lesionados
            $table->unsignedTinyInteger('games_missed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('injuries');
    }
};
