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

}
