<?php

if (!function_exists('setting_image')) {
    /**
     * Get a resolved setting image URL.
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    function setting_image($key, $default = null) {
        return \App\Helpers\SettingHelper::image($key, $default);
    }
}

if (!function_exists('setting_sanitized')) {
    /**
     * Get a sanitized dynamic setting string (prevent Stored XSS).
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    function setting_sanitized($key, $default = null) {
        return \App\Helpers\SettingHelper::sanitized($key, $default);
    }
}

if (!function_exists('setting_social_links')) {
    /**
     * Get dynamic site social links.
     *
     * @return array
     */
    function setting_social_links() {
        return \App\Helpers\SettingHelper::socialLinks();
    }
}
