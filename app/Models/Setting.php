<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const DEFAULTS = [
        'site_name' => 'Dynamic Details',
        'theme_color' => '#2563eb',
        'site_logo' => null,
        'site_favicon' => null,
    ];

    public static function allSettings(): array
    {
        return Cache::rememberForever('app_settings', function (): array {
            if (! Schema::hasTable('settings')) {
                return self::DEFAULTS;
            }

            return array_replace(
                self::DEFAULTS,
                self::query()->pluck('value', 'key')->all()
            );
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return self::allSettings()[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_settings');
    }
}
