<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lesson16Seeder extends Seeder
{
    private const CREATOR_EMAIL = 'lesson16@example.test';

    public function run(): void
    {
        DB::transaction(function (): void {
            $creator = User::firstOrCreate(['email' => self::CREATOR_EMAIL], [
                'name' => 'Nhân viên mẫu Buổi 16',
                'password' => Str::random(40),
            ]);

            if (! $creator->wasRecentlyCreated) {
                $this->command?->info('Dữ liệu mẫu Buổi 16 đã được tạo.');

                return;
            }

            $initialStock = 50;
            $ordersPerCustomer = 5;
            $quantity = 2;

            $samples = [
                ['customer' => 'Nguyễn Minh An', 'sku' => 'LESSON16-MOUSE', 'product' => 'Chuột có dây', 'price' => 120000],
                ['customer' => 'Trần Thu Hà', 'sku' => 'LESSON16-KEYBOARD', 'product' => 'Bàn phím USB', 'price' => 250000],
            ];

            foreach ($samples as $sample) {
                $customer = Customer::factory()->for($creator, 'creator')->create([
                    'name' => $sample['customer'],
                    'email' => null,
                ]);

                $product = Product::factory()->create([
                    'sku' => $sample['sku'],
                    'name' => $sample['product'],
                    'price_vnd' => $sample['price'],
                    'stock' => $initialStock - $ordersPerCustomer * $quantity,
                ]);

                StockMovement::factory()->for($product)->for($creator, 'actor')->create([
                    'delta' => $initialStock,
                ]);

                $orders = Order::factory()->count($ordersPerCustomer)
                    ->for($customer)->for($creator, 'creator')->create([
                        'status' => 'confirmed',
                        'total_vnd' => $sample['price'] * $quantity,
                        'confirmed_at' => now(),
                    ]);

                foreach ($orders as $order) {
                    OrderItem::factory()->for($order)->for($product)->create([
                        'quantity' => $quantity,
                    ]);

                    StockMovement::factory()->for($product)->for($order)->for($creator, 'actor')->create([
                        'delta' => -$quantity,
                        'reason' => 'sale',
                    ]);
                }

                $product->update([
                    'name' => $sample['product'].' (mới)',
                    'price_vnd' => $sample['price'] + 10000,
                ]);
            }
        });
    }
}
