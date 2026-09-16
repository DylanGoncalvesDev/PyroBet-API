<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SportMatch;
use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PredictionApiTest extends TestCase
{
    use RefreshDatabase;
   
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/predictions');

       $response->assertStatus(401);
    }

     public function testUserCanListPredictions(): void
    {
        $player = User::factory()->create(['role' => 'user']);

        Passport::actingAs($player);

        $response = $this->getJson('/api/predictions');

        $response->assertStatus(200);
    }

    public function testUserCanCreatePredictions(): void
    {
       $player = User::factory()->create(['role' => 'user']);

       $match = SportMatch::factory()->create();

       Passport::actingAs($player);

       $response = $this->postJson('/api/predictions', [
            'match_id' => $match->id,
            'prediction' => 'home',
            'home_score_prediction' => 2,
            'away_score_prediction' => 1,
            'status' => 'pending'
       ]);

       $response->assertStatus(201);
    }

     public function testUserCanUpdatePredictions(): void
    {
        $player = User::factory()->create(['role' => 'user']);

        $match = SportMatch::factory()->create();
        
        $prediction = Prediction::create([
            'user_id' => $player->id,
            'match_id' => $match->id,
            'prediction' => 'home',
            'home_score_prediction' => 2,
            'away_score_prediction' => 1,
            'status' => 'pending'
        ]);

        Passport::actingAs($player);

        $response = $this->putJson("/api/predictions/{$prediction->id}", [
            'prediction' => 'draw',
            'home_score_prediction' => 1,
            'away_score_prediction' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->id,
            'prediction' => 'draw',
            'home_score_prediction' => 1,
            'away_score_prediction' => 1,
        ]);
    }
}
