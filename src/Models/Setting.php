<?php

namespace Nawasara\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key-value settings store for app-wide config (branding, feature flags, etc.).
 *
 * Usage:
 *   Setting::get('branding.app_name', 'Nawasara')
 *   Setting::set('branding.app_name', 'Kominfo Ponorogo')
 *   Setting::setFile('branding.logo', $uploadedFile)
 */
class Setting extends Model
{
    protected $table = 'nawasara_settings';

    protected $fillable = ['key', 'value', 'type'];

    protected const CACHE_KEY = 'nawasara_settings_all';
    protected const CACHE_TTL = 3600;

    public static function boot()
    {
        parent::boot();
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Get a setting value. Decodes JSON for json type.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::allCached();

        if (! isset($all[$key])) {
            return $default;
        }

        $row = $all[$key];
        $value = $row['value'];

        return match ($row['type']) {
            'bool' => (bool) $value,
            'json' => $value ? json_decode($value, true) : $default,
            default => $value ?? $default,
        };
    }

    /**
     * Set a setting value. Auto-detect type or pass explicit.
     */
    public static function set(string $key, mixed $value, ?string $type = null): void
    {
        $type = $type ?? match (true) {
            is_bool($value) => 'bool',
            is_array($value) => 'json',
            default => 'string',
        };

        $storedValue = match ($type) {
            'bool' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $storedValue, 'type' => $type]
        );
    }

    /**
     * Store an uploaded file and save its public URL.
     * Returns the URL stored.
     */
    public static function setFile(string $key, \Illuminate\Http\UploadedFile $file, string $disk = 'public', string $directory = 'branding'): string
    {
        $path = $file->store($directory, $disk);
        $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

        // Delete old file if exists
        $oldUrl = self::get($key);
        if ($oldUrl) {
            $oldPath = str_replace('/storage/', '', parse_url($oldUrl, PHP_URL_PATH));
            \Illuminate\Support\Facades\Storage::disk($disk)->delete($oldPath);
        }

        self::set($key, $url, 'file');
        return $url;
    }

    public static function forget(string $key): void
    {
        self::where('key', $key)->delete();
    }

    protected static function allCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::all()
                ->mapWithKeys(fn ($row) => [$row->key => ['value' => $row->value, 'type' => $row->type]])
                ->toArray();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
