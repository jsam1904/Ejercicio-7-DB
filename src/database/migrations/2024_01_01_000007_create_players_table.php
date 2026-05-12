<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            // Nullable: jugadores retirados o agentes libres no pertenecen a ningún equipo activo
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->enum('position', [
                'QB', 'RB', 'FB', 'WR', 'TE',
                'OT', 'OG', 'C',
                'DE', 'DT', 'NT',
                'OLB', 'ILB', 'MLB',
                'CB', 'FS', 'SS',
                'K', 'P', 'LS',
            ]);
            $table->unsignedTinyInteger('jersey_number')->nullable();
            $table->unsignedSmallInteger('height_inches');  // 74 = 6'2"
            $table->unsignedSmallInteger('weight_lbs');
            $table->date('date_of_birth');
            $table->string('college', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
