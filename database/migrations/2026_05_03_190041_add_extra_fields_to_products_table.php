<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price_ht', 10, 2)->nullable()->after('price_ht');
            $table->string('quality_grade')->nullable()->after('finish');
            $table->json('variations')->nullable()->after('tag');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sale_price_ht', 'quality_grade', 'variations']);
        });
    }
};
