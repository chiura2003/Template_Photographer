<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    public const CACHE_KEY = 'settings.current';

    public const HAS_TABLE_KEY = 'schema.settings';

    protected $fillable = [
        'photographer_name',
        'email',
        'phone',
        'bio',
        'profile_image',
        'instagram_url',
        'vimeo',
        'location',
    ];

    public static function current(): ?self
    {
        if (! static::tableExists()) {
            return null;
        }

        $attributes = Cache::rememberForever(
            static::CACHE_KEY,
            fn () => static::query()->first()?->getAttributes(),
        );

        return $attributes ? (new static)->newFromBuilder($attributes) : null;
    }

    public static function tableExists(): bool
    {
        return Cache::rememberForever(static::HAS_TABLE_KEY, fn () => Schema::hasTable('settings'));
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget(static::CACHE_KEY);
        });

        static::updating(function (Setting $setting) {
            if (! $setting->isDirty('profile_image')) {
                return;
            }

            $oldImage = $setting->getOriginal('profile_image');

            if ($oldImage) {
                Storage::disk('private')->delete($oldImage);
            }
        });

        static::deleted(function (Setting $setting) {
            Cache::forget(static::CACHE_KEY);

            if ($setting->profile_image) {
                Storage::disk('private')->delete($setting->profile_image);
            }
        });
    }
}
