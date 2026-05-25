<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Translation Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "openai", "deepl", "google"
    |
    */

    'provider' => env('TRANSLATION_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    */

    'locales' => [
        'fr' => ['name' => 'French', 'native_name' => 'Français', 'flag' => '🇫🇷'],
        'en' => ['name' => 'English', 'native_name' => 'English', 'flag' => '🇬🇧'],
    ],

    'default_locale' => env('APP_LOCALE', 'fr'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'fr'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_TRANSLATION_MODEL', 'gpt-4o-mini'),
        'temperature' => 0.3,
        'max_tokens' => 4000,
    ],

    /*
    |--------------------------------------------------------------------------
    | DeepL Configuration
    |--------------------------------------------------------------------------
    */

    'deepl' => [
        'api_key' => env('DEEPL_API_KEY'),
        'api_url' => env('DEEPL_API_URL', 'https://api-free.deepl.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */

    'cache_ttl' => env('TRANSLATION_CACHE_TTL', 86400),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => 'translations',
    'batch_size' => env('TRANSLATION_BATCH_SIZE', 10),

    /*
    |--------------------------------------------------------------------------
    | Translatable Models
    |--------------------------------------------------------------------------
    |
    | Define which models and fields should be translated automatically.
    |
    */

    'models' => [
        \App\Models\Product::class => [
            'fields' => ['name', 'description'],
            'slug_field' => 'slug',
        ],
        \App\Models\Universe::class => [
            'fields' => ['name', 'description'],
            'slug_field' => 'slug',
        ],
        \App\Models\Testimonial::class => [
            'fields' => ['author', 'shop', 'quote'],
        ],
        \App\Models\FaqItem::class => [
            'fields' => ['question', 'answer'],
        ],
        \App\Models\ContentBlock::class => [
            'fields' => ['data'],
            'is_json' => true,
        ],
        \App\Models\SiteSetting::class => [
            'fields' => ['value'],
            'is_json' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'translate_slugs' => true,
        'slug_max_length' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Translate
    |--------------------------------------------------------------------------
    |
    | Whether to automatically queue translations when models are created/updated.
    |
    */

    'auto_translate' => env('TRANSLATION_AUTO_TRANSLATE', false),

];
