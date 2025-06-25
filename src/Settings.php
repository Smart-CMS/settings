<?php

namespace SmartCms\Settings;

use SmartCms\Settings\Models\Setting;

class Settings
{
    protected static string $cachePath = 'bootstrap/cache/settings.php';

    public array $settings;

    public function __construct()
    {
        $this->settings = self::load();
    }

    public static function load(): array
    {
        $path = base_path(self::$cachePath);
        if (file_exists($path)) {
            return include $path;
        }
        $settings = [];

        Setting::all()->each(function ($setting) use (&$settings) {
            data_set($settings, $setting->key, $setting->value);
        });
        return $settings;
    }

    public function write(): void
    {
        $export = var_export($this->settings, true);

        file_put_contents(
            base_path(self::$cachePath),
            <<<PHP
                <?php

                return {$export};

            PHP
        );
    }

    public static function clear(): void
    {
        @unlink(base_path(self::$cachePath));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
