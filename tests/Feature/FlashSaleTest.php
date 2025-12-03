<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    /** @test */
    public function user_can_hold_a_product()
    {
        $product = Product::factory()->create(['stock_total' => 1]);

        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'qty' => 1
        ]);


        $response->assertStatus(201); // hold created
        $this->assertDatabaseHas('holds', [
            'product_id' => $product->id
        ]);
    }
}
