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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id', 'user_id');
            $table->foreignId('match_id')->constrained('matches', 'id', 'match_id');
            $table->enum('status', ['pending', 'correct', 'incorrect']);
            $table->enum('prediction', ['home', 'draw', 'away']);
            $table->integer('home_score_prediction');
            $table->integer('away_score_prediction');
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
