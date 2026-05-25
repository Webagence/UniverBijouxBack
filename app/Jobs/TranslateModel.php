<?php

namespace App\Jobs;

use App\Models\Translation;
use App\Models\TranslationBatch;
use App\Models\TranslationJob;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function __construct(
        public string $modelType,
        public string $modelId,
        public string $targetLocale,
        public ?string $sourceLocale = null,
        public ?string $batchId = null,
        public ?int $translationJobId = null
    ) {
        $this->onQueue(config('translation.queue', 'translations'));
    }

    public function handle(TranslationService $translationService): void
    {
        $model = $this->resolveModel();

        if (!$model) {
            Log::warning("Model not found for translation: {$this->modelType} {$this->modelId}");
            $this->markJobFailed('Model not found');
            return;
        }

        try {
            $translationService->translateModel($model, $this->targetLocale, $this->sourceLocale);

            if ($this->translationJobId) {
                $job = TranslationJob::find($this->translationJobId);
                if ($job) {
                    $job->markAsProcessed();
                }
            }

            if ($this->batchId) {
                $batch = TranslationBatch::find($this->batchId);
                if ($batch) {
                    $batch->incrementCompleted();
                }
            }

            Log::info("Successfully translated {$this->modelType} {$this->modelId} to {$this->targetLocale}");
        } catch (\Exception $e) {
            Log::error("Translation failed for {$this->modelType} {$this->modelId}: {$e->getMessage()}");

            if ($this->attempts() >= $this->tries) {
                $this->markJobFailed($e->getMessage());
            } else {
                $this->release($this->backoff);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Translation job permanently failed: {$this->modelType} {$this->modelId} to {$this->targetLocale} - {$exception->getMessage()}");
        $this->markJobFailed($exception->getMessage());
    }

    protected function resolveModel(): ?\Illuminate\Database\Eloquent\Model
    {
        if (!class_exists($this->modelType)) {
            return null;
        }

        return $this->modelType::find($this->modelId);
    }

    protected function markJobFailed(string $error): void
    {
        if ($this->batchId) {
            $batch = TranslationBatch::find($this->batchId);
            if ($batch) {
                $batch->incrementFailed();
            }
        }
    }
}
