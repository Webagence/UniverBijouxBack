<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('value', 10, 2)->default(0);
            $table->enum('applies_to', ['all', 'specific_products', 'specific_universes'])->default('all');
            $table->enum('billing_cycle', ['all', 'daily', 'weekly', 'monthly', 'yearly'])->default('all');
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->datetime('valid_from')->nullable();
            $table->datetime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_product', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['discount_id', 'product_id']);
        });

        Schema::create('discount_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('discount_id')->constrained('discounts')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['discount_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_user');
        Schema::dropIfExists('discount_product');
        Schema::dropIfExists('discounts');
    }
};
