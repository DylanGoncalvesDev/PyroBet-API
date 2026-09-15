<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_team_id')->constrained('teams', 'id', 'home_team_id');
            $table->foreignId('away_team_id')->constrained('teams', 'id', 'away_team_id');
            $table->dateTime('date');
            $table->string('location');
            $table->string('stage');
            $table->enum('status', ['upcoming', 'live', 'finished'])->default('upcoming');
            $table->integer('home_team_score')->nullable();
            $table->integer('away_team_score')->nullable();
            $table->enum('sport', ['american football', 'soccer football', 'rugby', 'basketball', 'baseball',
                'cricket', 'softball', 'volleyball', 'hockey', 'handball', 'futsal']);
            $table->foreignId('competition_id')->constrained('competitions', 'id', 'competition_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
