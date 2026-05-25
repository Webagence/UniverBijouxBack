<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // fr, en, es, etc.
            $table->string('name'); // French, English, etc.
            $table->string('native_name'); // Français, English, etc.
            $table->string('flag_emoji', 4)->nullable(); // 🇫🇷, 🇬🇧, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('direction', 3)->default('ltr'); // ltr, rtl
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
