<?php

namespace App\Services\Translation;

interface TranslatorInterface
{
    public function translate(string $text, string $sourceLocale, string $targetLocale, array $context = []): string;

    public function translateBatch(array $texts, string $sourceLocale, string $targetLocale, array $context = []): array;

    public function translateJson(array $data, string $sourceLocale, string $targetLocale, array $context = []): array;

    public function generateSlug(string $text, string $sourceLocale, string $targetLocale): string;

    public function getName(): string;
}
