<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->json('slugs')->nullable()->after('slug'); // {"fr": "colliers", "en": "necklaces"}
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('slugs')->nullable()->after('slug'); // {"fr": "bague-or", "en": "gold-ring"}
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->boolean('needs_translation')->default(false)->after('active');
        });

        Schema::table('faq_items', function (Blueprint $table) {
            $table->boolean('needs_translation')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('universes', function (Blueprint $table) {
            $table->dropColumn('slugs');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slugs');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn('needs_translation');
        });

        Schema::table('faq_items', function (Blueprint $table) {
            $table->dropColumn('needs_translation');
        });
    }
};
