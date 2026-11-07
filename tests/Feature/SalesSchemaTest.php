<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\IdempotencyRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SalesSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_can_persist_order_snapshots_and_signed_stock_movements(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => 'secret-password']);
        $customer = Customer::factory()->for($user, 'creator')->create(['email' => null]);
        $product = Product::factory()->create(['price_vnd' => 25000, 'stock' => 10]);
        $order = Order::factory()->for($customer)->for($user, 'creator')->create([
            'total_vnd' => 50000,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        $item = OrderItem::factory()->for($order)->for($product)->create(['quantity' => 2]);
        $movement = StockMovement::factory()->for($product)->for($order)->for($user, 'actor')->create(['delta' => -2]);
        $request = IdempotencyRequest::factory()->for($order)->for($user)->create();

        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertSame('admin', $user->fresh()->role);
        $this->assertSame($product->sku, $item->sku_snapshot);
        $this->assertSame($product->name, $item->name_snapshot);
        $this->assertSame(50000, $item->line_total_vnd);
        $this->assertSame($item->id, $order->items->sole()->id);
        $this->assertNotNull($order->fresh()->confirmed_at);
        $this->assertNull($order->cancelled_at);
        $this->assertSame(-2, $movement->fresh()->delta);
        $this->assertNotNull($movement->fresh()->created_at);
        $this->assertArrayNotHasKey('updated_at', $movement->getAttributes());
        $this->assertTrue($request->order->is($order));
        $product->update(['name' => 'Changed name', 'price_vnd' => 30000]);
        $this->assertSame(25000, $item->fresh()->unit_price_vnd);
    }

    public function test_customers_allow_duplicate_and_null_emails(): void
    {
        $user = User::factory()->create();
        Customer::factory()->count(2)->for($user, 'creator')->create(['email' => 'shared@example.com']);
        Customer::factory()->count(2)->for($user, 'creator')->create(['email' => null]);

        $this->assertSame(4, Customer::count());
        $this->assertSame('sales', $user->fresh()->role);
    }

    public function test_order_items_enforce_unique_product_per_order(): void
    {
        $item = OrderItem::factory()->create();

        $this->expectException(QueryException::class);
        OrderItem::factory()->create(['order_id' => $item->order_id, 'product_id' => $item->product_id]);
    }

    public function test_idempotency_key_is_scoped_to_user(): void
    {
        $request = IdempotencyRequest::factory()->create(['key' => 'same-key']);
        IdempotencyRequest::factory()->create(['key' => 'same-key']);

        $this->expectException(QueryException::class);
        IdempotencyRequest::factory()->create(['user_id' => $request->user_id, 'key' => 'same-key']);
    }

    public function test_product_sku_is_unique(): void
    {
        Product::factory()->create(['sku' => 'SKU-1']);

        $this->expectException(QueryException::class);
        Product::factory()->create(['sku' => 'SKU-1']);
    }

    public function test_customers_require_an_existing_creator(): void
    {
        $this->expectException(QueryException::class);
        Customer::factory()->create(['created_by' => 999999]);
    }
}
