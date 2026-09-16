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
}
