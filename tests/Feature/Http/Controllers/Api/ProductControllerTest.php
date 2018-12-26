<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @test
     */
    public function canCreateProduct()
    {
        //Given
            // user is authenticated
        //When
            // post request create product
        $response = $this->json('POST', '/api/products', [

        ]);
        //Then
            // product exists
        $response->assertStatus(201);

    }
}
