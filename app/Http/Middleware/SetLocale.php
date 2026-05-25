<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supportedLocales;
    protected string $defaultLocale;

    public function __construct()
    {
        $localesConfig = config('translation.locales', ['fr', 'en']);

        if (isset($localesConfig['fr'])) {
            $this->supportedLocales = array_keys($localesConfig);
        } else {
            $this->supportedLocales = $localesConfig;
        }

        $this->defaultLocale = config('translation.default_locale', 'fr');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->detectLocale($request);

        App::setLocale($locale);
        $request->setLocale($locale);

        return $next($request);
    }

    protected function detectLocale(Request $request): string
    {
        $locale = $this->detectFromQuery($request)
            ?? $this->detectFromHeader($request)
            ?? $this->detectFromCookie($request)
            ?? $this->detectFromBrowser($request)
            ?? $this->defaultLocale;

        return $this->sanitizeLocale($locale);
    }

    protected function detectFromQuery(Request $request): ?string
    {
        $locale = $request->query('locale') ?? $request->query('lang');

        if (is_string($locale) && $this->isSupported($locale)) {
            return $locale;
        }

        return null;
    }

    protected function detectFromHeader(Request $request): ?string
    {
        $locale = $request->header('X-Locale')
            ?? $request->header('X-Lang')
            ?? $request->header('Accept-Language');

        if (is_string($locale) && !empty($locale)) {
            if (str_contains($locale, ',')) {
                $locale = explode(',', $locale)[0];
            }
            $locale = explode('-', $locale)[0];
            $locale = strtolower(trim($locale));

            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        return null;
    }

    protected function detectFromCookie(Request $request): ?string
    {
        $locale = $request->cookie('locale') ?? $request->cookie('lang');

        if (is_string($locale) && $this->isSupported($locale)) {
            return $locale;
        }

        return null;
    }

    protected function detectFromBrowser(Request $request): ?string
    {
        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE');

        if (is_string($acceptLanguage) && !empty($acceptLanguage)) {
            $languages = explode(',', $acceptLanguage);

            foreach ($languages as $lang) {
                $locale = strtolower(explode('-', explode(';', $lang)[0])[0]);

                if ($this->isSupported($locale)) {
                    return $locale;
                }
            }
        }

        return null;
    }

    protected function isSupported(string $locale): bool
    {
        return in_array(strtolower($locale), array_map('strtolower', $this->supportedLocales));
    }

    protected function sanitizeLocale(string $locale): string
    {
        $locale = strtolower($locale);

        $map = [
            'en-us' => 'en',
            'en-gb' => 'en',
            'fr-fr' => 'fr',
            'fr-ca' => 'fr',
        ];

        return $map[$locale] ?? $locale;
    }
}
