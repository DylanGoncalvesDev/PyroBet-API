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
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/matches');

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
            'name' => 'Real Madrid',
            'logo' => 'realmadrid.png',
            'country' => 'España',
            'founded_at' => '1989',
            'type' => 'club',
            'sport' => 'soccer football',
       ]);

       $response->assertStatus(201);
    }

}
