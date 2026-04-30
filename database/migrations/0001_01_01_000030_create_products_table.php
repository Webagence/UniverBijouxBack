<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('universe_id')->nullable()->constrained('universes')->nullOnDelete();
            $table->decimal('price_ht', 10, 2)->default(0);
            $table->decimal('retail_ttc', 10, 2)->default(0);
            $table->decimal('vat_rate', 4, 2)->default(20.00);
            $table->integer('moq')->default(1);
            $table->integer('pack_size')->default(1);
            $table->integer('stock')->default(0);
            $table->json('images')->nullable();
            $table->string('material')->nullable();
            $table->string('finish')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('locale');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
