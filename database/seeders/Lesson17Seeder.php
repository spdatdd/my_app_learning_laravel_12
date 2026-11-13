<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Lesson17Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Lesson17Seeder requires APP_ENV=local.');
        }

        DB::transaction(function (): void {
            User::factory()->admin()->create([
                'name' => 'Demo Admin',
                'email' => 'lesson17-admin@example.test',
                'password' => 'Lesson17-demo-only!',
            ]);

            foreach ([1, 2] as $number) {
                $salesUser = User::factory()->sales()->create([
                    'name' => "Demo Sales {$number}",
                    'email' => "lesson17-sales{$number}@example.test",
                    'password' => 'Lesson17-demo-only!',
                ]);

                Customer::factory()->count(5)->for($salesUser, 'creator')->create();
            }

            Product::factory()->count(20)->sequence(fn (Sequence $sequence): array => [
                'sku' => sprintf('LESSON17-%02d', $sequence->index + 1),
                'stock' => 0,
            ])->create();
        });
    }
}
