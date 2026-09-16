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
}
