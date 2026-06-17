<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $bijouxId = DB::table('sites')->where('slug', 'bijoux')->value('id');

        if (!$bijouxId) {
            $bijouxId = (string) Str::uuid();
            DB::table('sites')->insert([
                'id' => $bijouxId,
                'slug' => 'bijoux',
                'name' => 'Bijoux',
                'domain' => 'bijoux.francegems.com',
                'description' => 'Boutique de bijoux de luxe et artisanaux',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pierresId = DB::table('sites')->where('slug', 'pierres')->value('id');
        if (!$pierresId) {
            $pierresId = (string) Str::uuid();
            DB::table('sites')->insert([
                'id' => $pierresId,
                'slug' => 'pierres',
                'name' => 'Pierres Précieuses',
                'domain' => 'pierres.francegems.com',
                'description' => 'Boutique de pierres précieuses et semi-précieuses',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add site_id to universes (default to bijoux for existing)
        if (!Schema::hasColumn('universes', 'site_id')) {
            Schema::table('universes', function ($table) {
                $table->uuid('site_id')->nullable()->after('id');
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
            DB::table('universes')->whereNull('site_id')->update(['site_id' => $bijouxId]);
        }

        // Add site_id to products
        if (!Schema::hasColumn('products', 'site_id')) {
            Schema::table('products', function ($table) {
                $table->uuid('site_id')->nullable()->after('id');
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
            DB::table('products')->whereNull('site_id')->update(['site_id' => $bijouxId]);
        }

        // Add site_id to content_blocks
        if (!Schema::hasColumn('content_blocks', 'site_id')) {
            Schema::table('content_blocks', function ($table) {
                $table->uuid('site_id')->nullable()->after('id');
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
        }

        // Add site_id to testimonials
        if (!Schema::hasColumn('testimonials', 'site_id')) {
            Schema::table('testimonials', function ($table) {
                $table->uuid('site_id')->nullable()->after('id');
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
            DB::table('testimonials')->whereNull('site_id')->update(['site_id' => $bijouxId]);
        }

        // Add site_id to faq_items
        if (!Schema::hasColumn('faq_items', 'site_id')) {
            Schema::table('faq_items', function ($table) {
                $table->uuid('site_id')->nullable()->after('id');
                $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            });
            DB::table('faq_items')->whereNull('site_id')->update(['site_id' => $bijouxId]);
        }
    }

    public function down(): void
    {
        $tables = ['universes', 'products', 'content_blocks', 'testimonials', 'faq_items'];
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'site_id')) {
                Schema::table($table, function ($t) use ($table) {
                    $t->dropForeign([$table => 'site_id']);
                    $t->dropColumn('site_id');
                });
            }
        }
    }
};
