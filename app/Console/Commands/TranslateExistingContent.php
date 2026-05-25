<?php

namespace App\Console\Commands;

use App\Jobs\TranslateModel;
use App\Models\ContentBlock;
use App\Models\FaqItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Universe;
use Illuminate\Console\Command;

class TranslateExistingContent extends Command
{
    protected $signature = 'translate:existing
                            {--model= : Specific model to translate (Product, Universe, etc.)}
                            {--target= : Target locale (default: en)}
                            {--source= : Source locale (default: fr)}
                            {--all : Translate all models}';

    protected $description = 'Translate existing content to target locale using AI';

    protected array $modelMap = [
        'Product' => Product::class,
        'Universe' => Universe::class,
        'Testimonial' => Testimonial::class,
        'FaqItem' => FaqItem::class,
        'ContentBlock' => ContentBlock::class,
        'SiteSetting' => SiteSetting::class,
    ];

    public function handle(): int
    {
        $targetLocale = $this->option('target') ?? 'en';
        $sourceLocale = $this->option('source') ?? 'fr';
        $specificModel = $this->option('model');
        $all = $this->option('all');

        $this->info("Translating content from {$sourceLocale} to {$targetLocale}...");

        $modelsToTranslate = [];

        if ($specificModel) {
            if (!isset($this->modelMap[$specificModel])) {
                $this->error("Unknown model: {$specificModel}");
                $this->info("Available models: " . implode(', ', array_keys($this->modelMap)));
                return Command::FAILURE;
            }
            $modelsToTranslate[$specificModel] = $this->modelMap[$specificModel];
        } elseif ($all) {
            $modelsToTranslate = $this->modelMap;
        } else {
            $this->error("Please specify --model=ModelName or --all");
            $this->info("Available models: " . implode(', ', array_keys($this->modelMap)));
            return Command::FAILURE;
        }

        $totalJobs = 0;

        foreach ($modelsToTranslate as $name => $class) {
            $count = $class::count();
            $this->info("Queuing {$count} {$name}(s) for translation...");

            $class::cursor()->each(function ($model) use ($targetLocale, $sourceLocale, &$totalJobs) {
                TranslateModel::dispatch(
                    get_class($model),
                    $model->getKey(),
                    $targetLocale,
                    $sourceLocale
                );
                $totalJobs++;
            });
        }

        $this->info("Dispatched {$totalJobs} translation jobs.");
        $this->info("Run 'php artisan queue:work --queue=translations' to process them.");

        return Command::SUCCESS;
    }
}
