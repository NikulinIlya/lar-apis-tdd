<?php

namespace Tests\Feature\Http\Controllers\Api;

use Faker\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function nonAuthenticatedUsersCannotAccessFollowingEndpointsProductApi()
    {
        $index = $this->json('GET', '/api/products');
        $index->assertStatus(401);

        $store = $this->json('POST', '/api/products');
        $store->assertStatus(401);

        $show = $this->json('GET', '/api/products/-1');
        $show->assertStatus(401);

        $update = $this->json('PUT', '/api/products/-1');
        $update->assertStatus(401);

        $destroy = $this->json('DELETE', '/api/products/-1');
        $destroy->assertStatus(401);
    }

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
    public function willFailWithValidationErrorsWhenCreatingProductWithWrongInputs()
    {
        $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => $product->name,
            'price' => 'aaa'
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'price' => [
                        'The price must be an integer.'
                    ],
                    'name' => [
                        'The name has already been taken.'
                    ]
                ]
            ]);

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => '',
            'price' => 100
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ]
                ]
            ]);

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => str_random(65),
            'price' => 100
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name may not be greater than 64 characters.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     */
    public function willFailWithValidationErrorsWhenUpdatingProductWithWrongInputs()
    {
        $productOne = $this->create('Product');
        $productTwo = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "/api/products/$productTwo->id", ['name' => $productOne->name, 'price' => 'aaa']);

        $response->assertStatus(422)
        ->assertExactJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'price' => [
                    'The price must be an integer.'
                ],
                'name' => [
                    'The name has already been taken.'
                ]
            ]
        ]);

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "/api/products/$productTwo->id", ['name' => '', 'price' => 100]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ]
                ]
            ]);

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "/api/products/$productTwo->id", ['name' => str_random(65), 'price' => 100]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name may not be greater than 64 characters.'
                    ]
                ]
            ]);

        $productThree = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "/api/products/$productThree->id", ['name' => $productThree->name, 'price' => 100]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function canCreateProduct()
    {
        $faker = Factory::create();

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => $name = $faker->company,
            'slug' => str_slug($name),
            'price' => $price = random_int(10, 100)
        ]);

        //Then
            // product exists
        $response->assertJsonStructure([
            'id', 'image_id', 'name', 'slug', 'price', 'created_at'
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
    public function canCreateProductWithImage()
    {
        $faker = Factory::create();

        Storage::fake('public');

        $image = UploadedFile::fake()->image('image.jpg');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
            'name' => $name = $faker->company,
            'slug' => str_slug($name),
            'price' => $price = random_int(10, 100),
            'image' => $image
        ]);

        //Then
        // product exists
        $response->assertJsonStructure([
            'id', 'image_id', 'name', 'slug', 'price', 'created_at'
        ])
            ->assertJson([
                'name' => $name,
                'slug' => str_slug($name),
                'price' => $price
            ])
            ->assertStatus(201);

        Storage::disk('public')->assertExists("product_images/{$image->hashName()}");

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
            'image_id' => null,
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
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', 'api/products/-1', [
            'name' => 'test'
        ]);

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
            'image_id' => null,
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10,
            'created_at' => (string)$product->created_at
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_id' => null,
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
    public function canUpdateProductWithImage()
    {
        $product = $this->create('Product');

        Storage::fake('public');

        $image = UploadedFile::fake()->image('image.jpg');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "api/products/$product->id", [
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10,
            'image' => $image
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'id' => $product->id,
                'image_id' => json_decode($response->getContent())->image_id,
                'name' => $product->name.'_updated',
                'slug' => str_slug($product->name.'_updated'),
                'price' => $product->price + 10,
                'created_at' => (string)$product->created_at
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image_id' => json_decode($response->getContent())->image_id,
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
