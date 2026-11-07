<?php

namespace Database\Seeders;

use App\Models\IdempotencyRequest;
use Illuminate\Database\Seeder;

class IdempotencyRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IdempotencyRequest::factory()->count(10)->create();
    }
}
