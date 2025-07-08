<?php

namespace SmartCms\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property mixed $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public static function get(string $key = '*', mixed $default = null): mixed
    {
        $settings = [];

        Setting::all()->each(function ($setting) use (&$settings) {
            data_set($settings, $setting->key, $setting->value);
        });

        if ($key === '*') {
            return $settings;
        }

        return data_get($settings, $key, $default);
    }

    public static function set(string $key, mixed $value): mixed
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        cache()->forget('smart_cms_settings');

        return $setting->value;
    }

    public function getTable()
    {
        return config('settings.database_table_name');
    }

    protected static function booted(): void
    {
        static::updated(function () {
            app('s')->reload();
            app('s')->write();
        });
        static::created(function () {
            app('s')->reload();
            app('s')->write();
        });
    }
}
