<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    private const CACHE_KEY = 'app_settings_v2';
    private const LEGACY_SITE_NAMES = ['Dynamic' . ' Details'];

    public const DEFAULTS = [
        'site_name' => 'ADMS',
        'theme_color' => '#2563eb',
        'site_logo' => null,
        'site_favicon' => null,
    ];

    public static function allSettings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            if (! Schema::hasTable('settings')) {
                return self::DEFAULTS;
            }

            $settings = array_replace(
                self::DEFAULTS,
                self::query()->pluck('value', 'key')->all()
            );

            if (in_array($settings['site_name'] ?? null, self::LEGACY_SITE_NAMES, true)) {
                $settings['site_name'] = self::DEFAULTS['site_name'];
            }

            return $settings;
        });
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return self::allSettings()[$key] ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }
}
