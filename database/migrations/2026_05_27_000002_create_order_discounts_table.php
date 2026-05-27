<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignUuid('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            $table->string('code')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'free_shipping']);
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('amount_ht', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_ht', 10, 2)->default(0)->after('subtotal_ht');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('discount_ht');
        });
        Schema::dropIfExists('order_discounts');
    }
};
