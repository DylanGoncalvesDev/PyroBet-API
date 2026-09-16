<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CompetitionApiTest extends TestCase
{
    public function testCannotAccessWithoutAuthenticationToken(): void
    {
       $response = $this->getJson('/api/competitions');

       $response->assertStatus(401);
    }
}
