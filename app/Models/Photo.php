<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class Photo extends Model
{
    public const HOMEPAGE_LIMIT = 18;

    protected $fillable = [
        'album_id',
        'title',
        'description',
        'filename',
        'filepath',
        'mime_type',
        'filesize',
        'width',
        'height',
        'alt_text',
        'sort_order',
        'is_published',
        'is_homepage',
        'homepage_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_homepage' => 'boolean',
            'homepage_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Photo $photo) {
            if ($photo->filepath && ($photo->isDirty('filepath') || empty($photo->filename))) {
                $photo->filename = basename($photo->filepath);
            }

            if ($photo->is_homepage && (int) ($photo->homepage_order ?? 0) < 1) {
                $photo->homepage_order = ((int) static::query()
                    ->where('is_homepage', true)
                    ->when($photo->exists, fn ($query) => $query->where($photo->getKeyName(), '!=', $photo->getKey()))
                    ->max('homepage_order')) + 1;
            }

            if ($photo->isDirty('is_homepage') && $photo->is_homepage && ! $photo->getOriginal('is_homepage')) {
                $homepageCount = static::query()
                    ->where('is_homepage', true)
                    ->when($photo->exists, fn ($query) => $query->where($photo->getKeyName(), '!=', $photo->getKey()))
                    ->count();

                if ($homepageCount >= static::HOMEPAGE_LIMIT) {
                    throw ValidationException::withMessages([
                        'is_homepage' => static::homepageLimitMessage(),
                    ]);
                }
            }

            if ($photo->isDirty('is_homepage') && ! $photo->is_homepage) {
                $photo->homepage_order = 0;
            }
        });

        static::deleting(function (Photo $photo) {
            // Cancella il file associato al percorso salvato.
            if ($photo->filepath) {
                Storage::disk('public')->delete($photo->filepath);
            }
        });

        static::updating(function (Photo $photo) {
            // Se la foto e' stata cambiata, elimina il vecchio file.
            if ($photo->isDirty('filepath')) {
                $old = $photo->getOriginal('filepath');

                if ($old) {
                    Storage::disk('public')->delete($old);
                }
            }
        });
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->filepath
            ? Storage::disk('public')->url($this->filepath)
            : null;
    }

    public static function homepageCount(): int
    {
        return static::query()
            ->where('is_homepage', true)
            ->count();
    }

    public static function homepageSlotsAvailable(): bool
    {
        return static::homepageCount() < static::HOMEPAGE_LIMIT;
    }

    public static function homepageLimitMessage(): string
    {
        return 'Limite Homepage raggiunto: puoi selezionare massimo '.static::HOMEPAGE_LIMIT.' foto.';
    }
}
