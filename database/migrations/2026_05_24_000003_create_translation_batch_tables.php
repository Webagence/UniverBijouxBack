<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // "Product translations - May 2026"
            $table->string('source_locale', 5); // fr
            $table->string('target_locale', 5); // en
            $table->integer('total_items')->default(0);
            $table->integer('completed_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('translation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('batch_id')->nullable()->constrained('translation_batches')->nullOnDelete();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->string('locale', 5);
            $table->integer('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_jobs');
        Schema::dropIfExists('translation_batches');
    }
};
