<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $this->artisan('passport:install');
    }

    /**
     * @test
     */
    public function canAuthenticate()
    {
        $response = $this->json('POST', '/auth/token', [
            'email' => $this->create('User', [], false)->email,
            'password' => 'secret'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }
}