<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasIndex('orders', 'orders_created_by_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['created_by', 'status'], 'orders_created_by_status_index');
            });
        }

        if (! Schema::hasIndex('orders', 'orders_confirmed_at_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('confirmed_at', 'orders_confirmed_at_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('orders', 'orders_confirmed_at_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_confirmed_at_index');
            });
        }

        if (Schema::hasIndex('orders', 'orders_created_by_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_created_by_status_index');
            });
        }
    }
};
