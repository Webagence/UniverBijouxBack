<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize {--dry-run : Afficher ce qui sera fait sans modifier}';
    protected $description = 'Optimise toutes les images uploadées (conversion WebP + redimensionnement)';

    public function handle(ImageOptimizer $optimizer): int
    {
        $dryRun = $this->option('dry-run');
        $total = 0;
        $saved = 0;

        // Products images
        $this->info('Scanning product images...');
        foreach (Product::cursor() as $product) {
            $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
            if (!is_array($images)) continue;
            foreach ($images as &$img) {
                $result = $this->optimizePath($img, $optimizer, $dryRun);
                if ($result && $result !== $img) {
                    $img = $result;
                    $product->images = $images;
                    $product->saveQuietly();
                    $saved++;
                }
                $total++;
            }
        }

        // Settings logo
        $this->info('Scanning settings logo...');
        $settings = DB::table('site_settings')->get();
        foreach ($settings as $setting) {
            $data = json_decode($setting->value, true) ?? [];
            if (!empty($data['logo'])) {
                $result = $this->optimizeUrl($data['logo'], $optimizer, $dryRun);
                if ($result) {
                    $data['logo'] = $result;
                    DB::table('site_settings')->where('id', $setting->id)->update(['value' => json_encode($data)]);
                    $saved++;
                }
                $total++;
            }
        }

        // Content blocks
        $this->info('Scanning content block images...');
        $blocks = DB::table('content_blocks')->get();
        foreach ($blocks as $block) {
            $data = json_decode($block->data, true) ?? [];
            $changed = false;
            foreach (['image', 'hero_image', 'univ_gems_image', 'univ_jewelry_image'] as $key) {
                if (!empty($data[$key])) {
                    $result = $this->optimizeUrl($data[$key], $optimizer, $dryRun);
                    if ($result) {
                        $data[$key] = $result;
                        $changed = true;
                        $saved++;
                    }
                    $total++;
                }
            }
            if ($changed) {
                DB::table('content_blocks')->where('id', $block->id)->update(['data' => json_encode($data)]);
            }
        }

        $this->info("Done. Scanned: {$total}, Optimized: {$saved}" . ($dryRun ? ' (dry-run)' : ''));
        return Command::SUCCESS;
    }

    protected function optimizePath(string &$path, ImageOptimizer $optimizer, bool $dryRun): ?string
    {
        if (str_starts_with($path, 'http')) {
            return null;
        }
        $fullPath = storage_path("app/public/{$path}");
        if (!file_exists($fullPath)) return null;
        if (str_ends_with($path, '.webp')) return null;

        if ($dryRun) {
            $this->warn("  Would optimize: {$path}");
            return null;
        }

        $result = $optimizer->optimize($fullPath);
        if ($result) {
            $this->info("  ✅ {$path} -> {$result}");
            return $result;
        }
        return null;
    }

    protected function optimizeUrl(string $url, ImageOptimizer $optimizer, bool $dryRun): ?string
    {
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? '', '/storage/');
        $fullPath = storage_path("app/public/{$path}");
        if (!file_exists($fullPath)) return null;
        if (str_ends_with($path, '.webp')) return null;

        if ($dryRun) {
            $this->warn("  Would optimize: {$path}");
            return null;
        }

        $result = $optimizer->optimizeFromUrl($url);
        if ($result) {
            $this->info("  ✅ {$path} -> " . basename($result));
            return $result;
        }
        return null;
    }
}
