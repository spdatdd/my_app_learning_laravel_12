<?php

namespace Database\Factories;

use App\Models\IdempotencyRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdempotencyRequest>
 */
class IdempotencyRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'key' => fake()->uuid(),
            'request_hash' => hash('sha256', fake()->uuid()),
            'order_id' => Order::factory(),
        ];
    }
}
