<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class DeepLTranslator implements TranslatorInterface
{
    protected string $apiKey;
    protected string $apiUrl;

    protected array $localeMap = [
        'en' => 'EN-US',
        'fr' => 'FR',
        'de' => 'DE',
        'es' => 'ES',
        'it' => 'IT',
        'pt' => 'PT-PT',
        'nl' => 'NL',
        'pl' => 'PL',
        'ru' => 'RU',
        'ja' => 'JA',
        'zh' => 'ZH',
        'ko' => 'KO',
        'ar' => 'AR',
        'bg' => 'BG',
        'cs' => 'CS',
        'da' => 'DA',
        'el' => 'EL',
        'et' => 'ET',
        'fi' => 'FI',
        'hu' => 'HU',
        'id' => 'ID',
        'lv' => 'LV',
        'lt' => 'LT',
        'nb' => 'NB',
        'ro' => 'RO',
        'sk' => 'SK',
        'sl' => 'SL',
        'sv' => 'SV',
        'tr' => 'TR',
        'uk' => 'UK',
    ];

    public function __construct()
    {
        $this->apiKey = config('translation.deepl.api_key');
        $this->apiUrl = config('translation.deepl.api_url', 'https://api-free.deepl.com');
    }

    public function getName(): string
    {
        return 'deepl';
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale, array $context = []): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $targetLang = $this->mapLocale($targetLocale);
        $sourceLang = $this->mapLocale($sourceLocale);

        $response = Http::withHeaders([
            'Authorization' => "DeepL-Auth-Key {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/v2/translate", [
            'text' => [$text],
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'preserve_formatting' => true,
            'formality' => 'prefer_more',
        ]);

        if ($response->failed()) {
            throw new Exception("DeepL API error: {$response->body()}");
        }

        $result = $response->json('translations.0.text');

        return trim($result);
    }

    public function translateBatch(array $texts, string $sourceLocale, string $targetLocale, array $context = []): array
    {
        $nonEmptyTexts = array_filter($texts, fn($t) => !empty(trim($t)));

        if (empty($nonEmptyTexts)) {
            return $texts;
        }

        $targetLang = $this->mapLocale($targetLocale);
        $sourceLang = $this->mapLocale($sourceLocale);

        $response = Http::withHeaders([
            'Authorization' => "DeepL-Auth-Key {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/v2/translate", [
            'text' => array_values($nonEmptyTexts),
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'preserve_formatting' => true,
            'formality' => 'prefer_more',
        ]);

        if ($response->failed()) {
            throw new Exception("DeepL API error: {$response->body()}");
        }

        $translations = $response->json('translations');
        $translatedValues = array_column($translations, 'text');

        $result = [];
        $translationIndex = 0;
        foreach ($texts as $key => $text) {
            if (!empty(trim($text))) {
                $result[$key] = trim($translatedValues[$translationIndex] ?? $text);
                $translationIndex++;
            } else {
                $result[$key] = $text;
            }
        }

        return $result;
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

    public function generateSlug(string $text, string $sourceLocale, string $targetLocale): string
    {
        $translated = $this->translate($text, $sourceLocale, $targetLocale);
        $slug = Str::slug($translated, '-', $targetLocale);

        $maxLength = config('translation.seo.slug_max_length', 60);
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    protected function mapLocale(string $locale): string
    {
        $baseLocale = substr($locale, 0, 2);
        return $this->localeMap[$baseLocale] ?? strtoupper($baseLocale);
    }
}
