<?php

namespace Tests\Feature;

use Tests\TestCase;

class FirstApiFeatureTest extends TestCase
{
    public function test_health_returns_ok_status_in_json(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
            ]);
    }

    public function test_products_returns_three_sample_products(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'price'],
                ],
            ])
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.name', 'Keyboard')
            ->assertJsonPath('data.0.price', 350000);
    }

    public function test_customer_accepts_valid_data(): void
    {
        $payload = [
            'name' => 'Nguyen Van An',
            'email' => 'an@example.com',
            'phone' => '0901234567',
        ];

        $response = $this->postJson('/api/v1/customers', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', $payload['name'])
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.phone', $payload['phone'])
            ->assertJsonPath(
                'message',
                'Du lieu hop le. Khach hang chua duoc luu vao database.'
            );
    }

    public function test_customer_rejects_data_without_name(): void
    {
        $payload = [
            'email' => 'an@example.com',
            'phone' => '0901234567',
        ];

        $response = $this->postJson('/api/v1/customers', $payload);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['email', 'phone']);
    }
}
