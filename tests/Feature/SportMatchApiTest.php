<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SportMatch;
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
    
}
