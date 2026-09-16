<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
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

    protected static function booted(): void
    {
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
            if ($setting->profile_image) {
                Storage::disk('private')->delete($setting->profile_image);
            }
        });
    }
}
