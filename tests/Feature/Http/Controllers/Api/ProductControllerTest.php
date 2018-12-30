<?php

namespace Tests\Feature\Http\Controllers\Api;

use Faker\Factory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function canReturnPaginatedProductsCollection()
    {
        $product1 = $this->create('Product');
        $product2 = $this->create('Product');
        $product3 = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', '/api/products');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug', 'price', 'created_at']
            ],

            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => [
                'current_page', 'last_page', 'from', 'to', 'path', 'per_page', 'total'
            ]
        ]);
    }


    /**
     * @test
     */
    public function canCreateProduct()
    {
        //Given
            // user is authenticated

        //When
            // post request create product
        $faker = Factory::create();

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => $name = $faker->company,
            'slug' => str_slug($name),
            'price' => $price = random_int(10, 100)
        ]);

        //Then
            // product exists
        $response->assertJsonStructure([
            'id', 'name', 'slug', 'price', 'created_at'
        ])
        ->assertJson([
            'name' => $name,
            'slug' => str_slug($name),
            'price' => $price
        ])
        ->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => $name,
            'slug' => str_slug($name),
            'price' => $price
        ]);
    }

    /**
     * @test
     */
    public function willFailWith404IfProductIsNotFound()
    {
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', 'api/products/-1');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function canReturnProduct()
    {
        // Given
        $product = $this->create('Product');

        // When
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', "api/products/$product->id");

        // Then
        $response->assertStatus(200)
        ->assertExactJson([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'created_at' => (string)$product->created_at
        ]);
    }

    /**
     * @test
     */
    public function willFailWith404IfProductToUpdateIsNotFound()
    {
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', 'api/products/-1');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function canUpdateProduct()
    {
        $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "api/products/$product->id", [
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10
        ]);

        $response->assertStatus(200)
        ->assertExactJson([
            'id' => $product->id,
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10,
            'created_at' => (string)$product->created_at
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10,
            'created_at' => (string)$product->created_at,
            'updated_at' => (string)$product->updated_at,
        ]);
    }

    /**
     * @test
     */
    public function willFailWith404IfProductToDeleteIsNotFound()
    {
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('DELETE', 'api/products/-1');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function canDeleteProduct()
    {
        $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('DELETE', "api/products/$product->id");

        $response->assertStatus(204)
            ->assertSee(null);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
