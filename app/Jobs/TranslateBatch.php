<?php

namespace App\Jobs;

use App\Models\TranslationBatch;
use App\Models\TranslationJob;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public string $batchId
    ) {
        $this->onQueue(config('translation.queue', 'translations'));
    }

    public function handle(TranslationService $translationService): void
    {
        $batch = TranslationBatch::find($this->batchId);

        if (!$batch) {
            Log::warning("Translation batch not found: {$this->batchId}");
            return;
        }

        $batch->markAsStarted();

        $jobs = TranslationJob::where('batch_id', $this->batchId)
            ->whereNull('processed_at')
            ->get();

        foreach ($jobs as $job) {
            TranslateModel::dispatch(
                $job->model_type,
                $job->model_id,
                $job->locale,
                $batch->source_locale,
                $batch->id,
                $job->id
            );
        }

        Log::info("Translation batch {$this->batchId} dispatched with {$jobs->count()} jobs");
    }

    public function failed(\Throwable $exception): void
    {
        $batch = TranslationBatch::find($this->batchId);
        if ($batch) {
            $batch->markAsFailed($exception->getMessage());
        }

        Log::error("Translation batch failed: {$this->batchId} - {$exception->getMessage()}");
    }
}
