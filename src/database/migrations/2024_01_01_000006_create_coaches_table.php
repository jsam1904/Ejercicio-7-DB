<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->enum('role', [
                'head_coach',
                'offensive_coordinator',
                'defensive_coordinator',
                'special_teams_coordinator',
                'quarterbacks_coach',
                'running_backs_coach',
                'wide_receivers_coach',
                'linebackers_coach',
                'defensive_backs_coach',
            ]);
            $table->date('hire_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};
