<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shippingbo_order_id')->nullable()->after('stripe_payment_status');
            $table->timestamp('shippingbo_synced_at')->nullable()->after('shippingbo_order_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('shippingbo_product_id')->nullable()->after('stock');
            $table->timestamp('shippingbo_synced_at')->nullable()->after('shippingbo_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shippingbo_order_id', 'shippingbo_synced_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shippingbo_product_id', 'shippingbo_synced_at']);
        });
    }
};
