<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TeamsApiTest extends TestCase
{
    use RefreshDatabase;
    
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/teams');

       $response->assertStatus(401);
    }

     public function testUserCanListTeams(): void
    {
        $player = User::factory()->create(['role' => 'admin']);

        $team = Team::factory()->create();

        Passport::actingAs($player);

        $response = $this->getJson('/api/teams');

        $response->assertStatus(200);
    }

    public function testUserCanCreateTeams(): void
    {
       $player = User::factory()->create(['role' => 'admin']);

       Passport::actingAs($player);

       $response = $this->postJson('/api/teams', [
            'name' => 'Real Madrid FC',
            'logo' => 'realmadrid.png',
            'country' => 'España',
            'founded_at' => '1989',
            'type' => 'club',
            'sport' => 'soccer football',
       ]);

       $response->assertStatus(201);
    }

     public function testUserCanUpdateTeams(): void
    {
        $player = User::factory()->create(['role' => 'admin']);
        
        $team = Team::create([
            'name' => 'Real Madrid FC',
            'logo' => 'realmadrid.png',
            'country' => 'España',
            'founded_at' => '1989',
            'type' => 'club',
            'sport' => 'soccer football',
        ]);

        Passport::actingAs($player);

        $response = $this->putJson("/api/teams/{$team->id}", [
            'name' => 'Barcelona FC',
            'logo' => 'barca.png',
            'founded_at' => '1888',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Barcelona FC',
            'logo' => 'barca.png',
            'founded_at' => '1888',
        ]);
    }

     public function testUserCanDeleteTeams(): void
    {
        $player = User::factory()->create(['role' => 'admin']);
        
         $team = Team::create([
            'name' => 'Real Madrid FC',
            'logo' => 'realmadrid.png',
            'country' => 'España',
            'founded_at' => '1989',
            'type' => 'club',
            'sport' => 'soccer football',
        ]);

        Passport::actingAs($player);

        $response = $this->deleteJson("/api/teams/{$team->id}");

        $response->assertStatus(200);
    }
}
