<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropUnique('content_blocks_key_unique');
        });
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->unique(['key', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropUnique(['key', 'site_id']);
        });
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->string('key')->unique();
        });
    }
};
