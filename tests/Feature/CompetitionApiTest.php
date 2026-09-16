<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Competition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CompetitionApiTest extends TestCase
{
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/competitions');

       $response->assertStatus(401);
    }

    public function testUserCanListCompetitions(): void
    {
        $player = User::factory()->create(['role' => 'admin']);

        $competition = Competition::factory()->create();

        Passport::actingAs($player);

        $response = $this->getJson('/api/competitions');

        $response->assertStatus(200);
    }

     public function testUserCanCreateCompetitions(): void
    {
       $player = User::factory()->create(['role' => 'admin']);

       Passport::actingAs($player);

       $response = $this->postJson('/api/competitions', [
            'name' => 'La Liga',
            'status' => 'in_progress',
            'start_date' => '2026-08-25 00:00:00',
            'end_date' => '2027-07-15 00:00:00',
       ]);

       $response->assertStatus(201);
    }

    public function testUserCanUpdateCompetitions(): void
    {
        $player = User::factory()->create(['role' => 'admin']);
        
        $competition = Competition::create([
            'name' => 'La Liga',
            'status' => 'in_progress',
            'start_date' => '2026-08-25 00:00:00',
            'end_date' => '2027-07-15 00:00:00',
        ]);

        Passport::actingAs($player);

        $response = $this->putJson("/api/competitions/{$competition->id}", [
            'name' => 'Serie A',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'name' => 'Serie A',
            'status' => 'in_progress',
        ]);
    }


}
