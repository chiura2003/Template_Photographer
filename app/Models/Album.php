<?php

namespace App\Models;

use App\Enums\AlbumType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Album extends Model
{
    protected $fillable = [
        'title',
        'year',
        'type',
        'slug',
        'description',
        'cover_photo_id',
        'featured',
        'is_published',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (Album $album) {
            if (empty($album->slug)) {
                $album->slug = Str::slug($album->title);
            }
        });

        static::created(function (Album $album) {
            Storage::disk('public')->makeDirectory(
                'albums/'.$album->slug
            );
        });

        static::updating(function (Album $album) {
            if (! $album->isDirty('title')) {
                return;
            }

            $oldSlug = $album->getOriginal('slug');
            $newSlug = Str::slug($album->title);

            $album->slug = $newSlug;

            if (! $oldSlug || $oldSlug === $newSlug) {
                return;
            }

            $disk = Storage::disk('public');

            $oldDirectory = 'albums/'.$oldSlug;
            $newDirectory = 'albums/'.$newSlug;

            if ($disk->exists($newDirectory)) {
                throw new \RuntimeException(
                    "Esiste già una cartella per l'album '{$newSlug}'."
                );
            }

            $disk->makeDirectory($newDirectory);

            if ($disk->exists($oldDirectory)) {
                foreach ($disk->allFiles($oldDirectory) as $file) {
                    $relativePath = substr(
                        $file,
                        strlen($oldDirectory) + 1
                    );

                    $newFile = $newDirectory.'/'.$relativePath;

                    $disk->move($file, $newFile);
                }

                $disk->deleteDirectory($oldDirectory);
            }

            foreach ($album->photos as $photo) {
                if (str_starts_with($photo->filepath, $oldDirectory.'/')) {
                    $photo->filepath = $newDirectory.'/'.substr(
                        $photo->filepath,
                        strlen($oldDirectory) + 1
                    );

                    $photo->saveQuietly();
                }
            }
        });

        static::deleted(function (Album $album) {
            Storage::disk('public')->deleteDirectory(
                'albums/'.$album->slug
            );
        });
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function coverPhoto()
    {
        return $this->belongsTo(Photo::class, 'cover_photo_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'is_published' => 'boolean',
            'type' => AlbumType::class,
        ];
    }
}
