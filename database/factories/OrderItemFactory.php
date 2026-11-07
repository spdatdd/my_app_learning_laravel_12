<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'sku_snapshot' => fn (array $attributes): string => Product::findOrFail($attributes['product_id'])->sku,
            'name_snapshot' => fn (array $attributes): string => Product::findOrFail($attributes['product_id'])->name,
            'unit_price_vnd' => fn (array $attributes): int => Product::findOrFail($attributes['product_id'])->price_vnd,
            'quantity' => fake()->numberBetween(1, 5),
            'line_total_vnd' => fn (array $attributes): int => $attributes['unit_price_vnd'] * $attributes['quantity'],
        ];
    }
}
