<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingboSetting extends Model
{
    use HasFactory;

    protected $table = 'shippingbo_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function getValueAttribute(mixed $value): mixed
    {
        return json_decode($value ?? 'null', true);
    }

    public static function isConnected(): bool
    {
        return self::get('access_token') !== null;
    }

    public static function getApiBaseUrl(): string
    {
        return 'https://app.shippingbo.com';
    }

    public static function getOAuthBaseUrl(): string
    {
        return 'https://oauth.shippingbo.com';
    }

    public static function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-VERSION' => '1',
            'X-API-APP-ID' => self::get('app_id', ''),
            'Authorization' => 'Bearer ' . self::get('access_token', ''),
            'User-Agent' => 'UniverBijoux/1.0',
        ];
    }
}
