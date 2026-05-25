<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class OpenAITranslator implements TranslatorInterface
{
    protected string $apiKey;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('translation.openai.api_key');
        $this->model = config('translation.openai.model', 'gpt-4o-mini');
        $this->temperature = config('translation.openai.temperature', 0.3);
        $this->maxTokens = config('translation.openai.max_tokens', 4000);
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale, array $context = []): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $prompt = $this->buildPrompt($text, $sourceLocale, $targetLocale, $context);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ]);

        if ($response->failed()) {
            throw new Exception("OpenAI API error: {$response->body()}");
        }

        $result = $response->json('choices.0.message.content');

        return trim($result);
    }

    public function translateBatch(array $texts, string $sourceLocale, string $targetLocale, array $context = []): array
    {
        if (empty($texts)) {
            return [];
        }

        $indexedTexts = [];
        foreach ($texts as $key => $text) {
            if (!empty(trim($text))) {
                $indexedTexts[$key] = $text;
            }
        }

        if (empty($indexedTexts)) {
            return array_fill_keys(array_keys($texts), '');
        }

        $prompt = $this->buildBatchPrompt($indexedTexts, $sourceLocale, $targetLocale, $context);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt['system']],
                ['role' => 'user', 'content' => $prompt['user']],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->failed()) {
            throw new Exception("OpenAI API error: {$response->body()}");
        }

        $result = json_decode($response->json('choices.0.message.content'), true);

        $translated = [];
        foreach ($texts as $key => $text) {
            $translated[$key] = $result[$key] ?? $text;
        }

        return $translated;
    }

    public function translateJson(array $data, string $sourceLocale, string $targetLocale, array $context = []): array
    {
        $translated = $data;

        $translatableStrings = $this->extractTranslatableStrings($data);

        if (!empty($translatableStrings)) {
            $translatedStrings = $this->translateBatch(
                $translatableStrings,
                $sourceLocale,
                $targetLocale,
                $context
            );

            $translated = $this->replaceTranslatableStrings($data, $translatedStrings);
        }

        return $translated;
    }

    public function generateSlug(string $text, string $sourceLocale, string $targetLocale): string
    {
        $translated = $this->translate($text, $sourceLocale, $targetLocale, [
            'instruction' => 'Translate this to a SEO-friendly slug format (lowercase, hyphens, no special characters)',
        ]);

        $slug = Str::slug($translated, '-', $targetLocale);

        $maxLength = config('translation.seo.slug_max_length', 60);
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    protected function buildPrompt(string $text, string $sourceLocale, string $targetLocale, array $context = []): array
    {
        $localeNames = config('translation.locales', []);
        $sourceName = $localeNames[$sourceLocale]['name'] ?? $sourceLocale;
        $targetName = $localeNames[$targetLocale]['name'] ?? $targetLocale;

        $systemPrompt = "You are a professional translator specializing in e-commerce and jewelry. ";
        $systemPrompt .= "Translate from {$sourceName} to {$targetName}. ";
        $systemPrompt .= "Maintain the tone, style, and formatting. ";
        $systemPrompt .= "For jewelry/fashion content, use elegant and professional language. ";
        $systemPrompt .= "Return ONLY the translation, nothing else.";

        $userPrompt = $text;

        if (!empty($context['instruction'])) {
            $userPrompt = "{$context['instruction']}\n\n{$text}";
        }

        return [
            'system' => $systemPrompt,
            'user' => $userPrompt,
        ];
    }

    protected function buildBatchPrompt(array $texts, string $sourceLocale, string $targetLocale, array $context = []): array
    {
        $localeNames = config('translation.locales', []);
        $sourceName = $localeNames[$sourceLocale]['name'] ?? $sourceLocale;
        $targetName = $localeNames[$targetLocale]['name'] ?? $targetLocale;

        $systemPrompt = "You are a professional translator specializing in e-commerce and jewelry. ";
        $systemPrompt .= "Translate from {$sourceName} to {$targetName}. ";
        $systemPrompt .= "Maintain the tone, style, and formatting. ";
        $systemPrompt .= "For jewelry/fashion content, use elegant and professional language. ";
        $systemPrompt .= "Return a JSON object with the same keys as the input, containing only the translations.";

        $userPrompt = json_encode($texts, JSON_UNESCAPED_UNICODE);

        return [
            'system' => $systemPrompt,
            'user' => $userPrompt,
        ];
    }

    protected function extractTranslatableStrings(array $data, string $prefix = ''): array
    {
        $strings = [];

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                if ($this->isTranslatableArray($value)) {
                    foreach ($value as $index => $item) {
                        if (is_string($item) && !empty(trim($item))) {
                            $strings["{$fullKey}.{$index}"] = $item;
                        } elseif (is_array($item)) {
                            $strings = array_merge($strings, $this->extractTranslatableStrings($item, "{$fullKey}.{$index}"));
                        }
                    }
                } else {
                    $strings = array_merge($strings, $this->extractTranslatableStrings($value, $fullKey));
                }
            } elseif (is_string($value) && !empty(trim($value))) {
                $skipKeys = ['image', 'image_url', 'logo', 'icon', 'path', 'url', 'email', 'phone'];
                if (!$this->isSkippedKey($key, $skipKeys)) {
                    $strings[$fullKey] = $value;
                }
            }
        }

        return $strings;
    }

    protected function isTranslatableArray(array $value): bool
    {
        if (empty($value)) {
            return false;
        }

        $firstValue = reset($value);
        return !is_array($firstValue) || $this->isSimpleArray($value);
    }

    protected function isSimpleArray(array $value): bool
    {
        foreach ($value as $item) {
            if (is_array($item)) {
                return false;
            }
        }
        return true;
    }

    protected function isSkippedKey(string $key, array $skipKeys): bool
    {
        foreach ($skipKeys as $skip) {
            if (stripos($key, $skip) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function replaceTranslatableStrings(array $data, array $translations): array
    {
        $result = $data;

        foreach ($translations as $key => $translated) {
            $parts = explode('.', $key);
            $current = &$result;

            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    if (isset($current[$part]) && is_string($current[$part])) {
                        $current[$part] = $translated;
                    }
                } else {
                    if (isset($current[$part]) && is_array($current[$part])) {
                        $current = &$current[$part];
                    } else {
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
