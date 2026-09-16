<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\User;
use App\Models\SportMatch;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class SportMatchApiTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/matches');

       $response->assertStatus(401);
    }

    public function testUserCanListMatches(): void
    {
        $player = User::factory()->create(['role' => 'user']);

        $match = SportMatch::factory()->create();

        Passport::actingAs($player);

        $response = $this->getJson('/api/matches');

        $response->assertStatus(200);
    }

     public function testUserCanCreateMatches(): void
    {
       $player = User::factory()->create(['role' => 'admin']);

       $team = Team::factory()->create();
       $team2 = Team::factory()->create();

       $competition = Competition::factory()->create();

       Passport::actingAs($player);

       $response = $this->postJson('/api/matches', [
            'home_team_id' => $team->id,
            'away_team_id' => $team2->id,
            'date' => '2026-09-16 21:00:00',
            'location' => 'alianz arena',
            'stage' => 'jornada', 
            'status' => 'upcoming', 
            'sport' => 'soccer football', 
            'competition_id' => $competition->id
       ]);

       $response->assertStatus(201);
    }

     public function testUserCanUpdateMatches(): void
    {
        $player = User::factory()->create(['role' => 'admin']);

        $team = Team::factory()->create();
        $team2 = Team::factory()->create();

        $competition = Competition::factory()->create();

        $match = SportMatch::factory()->create([
            'home_team_id' => $team->id,
            'away_team_id' => $team2->id,
            'date' => '2026-09-16 21:00:00',
            'location' => 'alianz arena',
            'stage' => 'jornada', 
            'status' => 'upcoming', 
            'sport' => 'futbol', 
            'competition_id' => $competition->id
        ]);
        
        Passport::actingAs($player);

        $response = $this->putJson("/api/matches/{$match->id}", [
            'home_team_score' => 1,
            'away_team_score' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('matches', [
            'home_team_score' => 1,
            'away_team_score' => 1,
        ]);
    }

    public function testUserCanDeleteMatches(): void
    {
        $player = User::factory()->create(['role' => 'admin']);

        $team = Team::factory()->create();
        $team2 = Team::factory()->create();

        $competition = Competition::factory()->create();

        $match = SportMatch::factory()->create([
            'home_team_id' => $team->id,
            'away_team_id' => $team2->id,
            'date' => '2026-09-16 21:00:00',
            'location' => 'alianz arena',
            'stage' => 'jornada', 
            'status' => 'upcoming', 
            'sport' => 'futbol', 
            'competition_id' => $competition->id
        ]);

        Passport::actingAs($player);

        $response = $this->deleteJson("/api/matches/{$match->id}");

        $response->assertStatus(200);
    }
    
}
