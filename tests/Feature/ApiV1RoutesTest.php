<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApiV1RoutesTest extends TestCase
{
    public function test_health_returns_json(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_products_returns_sample_data_without_a_database(): void
    {
        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'price']]])
            ->assertJsonPath('data.0.name', 'Keyboard');
    }

    #[DataProvider('unsupportedMethods')]
    public function test_unsupported_methods_return_405(string $method, string $uri): void
    {
        $this->json($method, $uri)
            ->assertMethodNotAllowed()
            ->assertHeader('Allow', 'GET, HEAD');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function unsupportedMethods(): array
    {
        $cases = [];

        foreach (['health', 'products'] as $endpoint) {
            foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
                $cases[$method.' '.$endpoint] = [$method, '/api/v1/'.$endpoint];
            }
        }

        return $cases;
    }
}
