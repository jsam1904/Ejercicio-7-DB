<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stadiums', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('city', 100);
            $table->string('state', 50);
            $table->unsignedInteger('capacity');
            $table->enum('surface', ['grass', 'artificial_turf', 'field_turf']);
            $table->enum('roof_type', ['open', 'dome', 'retractable']);
            $table->unsignedSmallInteger('year_opened');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stadiums');
    }
};
