<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class SettingsService
{
    /**
     * Cache key for all settings.
     */
    const CACHE_KEY = 'settings.all';

    /**
     * Cache TTL in seconds (5 minutes).
     */
    const CACHE_TTL = 300;

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null): mixed
    {
        $settings = static::all();

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value): bool
    {
        $setting = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        static::clearCache();

        return $setting->wasRecentlyCreated || $setting->wasChanged();
    }

    /**
     * Get all settings as associative array.
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Check if pharmacy mode is enabled.
     */
    public static function pharmacyModeEnabled(): bool
    {
        return (bool) static::get('is_pharmacy_active', true);
    }

    /**
     * Enable pharmacy mode.
     */
    public static function enablePharmacyMode(): bool
    {
        return static::set('is_pharmacy_active', 'true');
    }

    /**
     * Disable pharmacy mode.
     */
    public static function disablePharmacyMode(): bool
    {
        return static::set('is_pharmacy_active', 'false');
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
