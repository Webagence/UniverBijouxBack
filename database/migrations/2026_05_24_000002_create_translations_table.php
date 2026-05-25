<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // App\Models\Product, App\Models\Universe, etc.
            $table->uuid('model_id');
            $table->string('locale', 5); // fr, en
            $table->string('field'); // name, description, question, answer, etc.
            $table->text('value');
            $table->enum('source', ['auto', 'manual'])->default('auto'); // auto (AI) or manual (human corrected)
            $table->enum('status', ['pending', 'translating', 'completed', 'failed'])->default('completed');
            $table->string('provider')->nullable(); // openai, deepl, google
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id', 'locale', 'field'], 'translation_unique');
            $table->index(['model_type', 'model_id'], 'translation_model');
            $table->index(['locale', 'status'], 'translation_locale_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
