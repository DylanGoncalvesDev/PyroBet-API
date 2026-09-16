<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeamsApiTest extends TestCase
{
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/matches');

       $response->assertStatus(401);
    }

}
