<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class SettingHelper
{
    /**
     * Get a setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return \Wave\Setting::get($key, $default);
    }

    /**
     * Get a sanitized HTML setting value (Stored XSS Protection).
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    public static function sanitized(string $key, ?string $default = null): string
    {
        $value = self::get($key, $default);

        if ($value === null || $value === '') {
            return '';
        }

        return Purifier::clean($value);
    }

    /**
     * Get a resolved image URL for a setting key.
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public static function image(string $key, ?string $default = null): ?string
    {
        $value = self::get($key);

        if (!$value) {
            return $default;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, 'settings/')) {
            return asset('storage/' . $value);
        }

        return asset($value);
    }

    /**
     * Get social media links as an array.
     *
     * @return array
     */
    public static function socialLinks(): array
    {
        $value = self::get('site_social_links');

        if (!$value) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true) ?: [];
    }
}
