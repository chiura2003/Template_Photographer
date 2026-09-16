<?php

namespace App\Services;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AlbumPhotoUploadService
{
    public const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public const MAX_IMAGE_SIZE = 51200;

    public const MAX_BULK_FILES = 300;

    /**
     * @param  array<string, mixed>  $data
     * @param  EloquentCollection<int, Album>|Collection<int, Album>  $albums
     * @return array{created: int, skippedDuplicates: int, skippedInvalid: int}
     */
    public function uploadToAlbums(array $data, EloquentCollection|Collection $albums): array
    {
        $uploadedPaths = $data['photos'] ?? [];
        $uploadedFileNames = $data['photo_file_names'] ?? [];

        if (! is_array($uploadedPaths)) {
            $uploadedPaths = [$uploadedPaths];
        }

        if (! is_array($uploadedFileNames)) {
            $uploadedFileNames = [];
        }

        $uploads = [];

        foreach ($uploadedPaths as $key => $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $uploads[] = [
                'path' => $path,
                'filename' => $this->normalizeFilename($uploadedFileNames[$key] ?? null, $path),
            ];
        }

        usort($uploads, fn (array $first, array $second): int => strnatcasecmp($first['filename'], $second['filename']));

        $validated = [];
        $skippedInvalid = 0;

        foreach ($uploads as $upload) {
            try {
                if (str_starts_with(basename($upload['path']), '._')) {
                    Storage::disk('public')->delete($upload['path']);
                    $skippedInvalid++;

                    continue;
                }

                $metadata = $this->readImageMetadata($upload['path']);

                if ($metadata === null) {
                    Storage::disk('public')->delete($upload['path']);
                    $skippedInvalid++;

                    continue;
                }

                $converted = app(WebpConverter::class)->convert($upload['path']);

                if ($converted !== null) {
                    $upload['path'] = $converted['path'];
                    $upload['filename'] = pathinfo($converted['path'], PATHINFO_BASENAME);
                    $metadata['mime_type'] = $converted['mime_type'];
                    $metadata['filesize'] = $converted['filesize'];
                }

                $validated[] = [...$upload, 'metadata' => $metadata];
            } catch (\Throwable) {
                Storage::disk('public')->delete($upload['path']);
                $skippedInvalid++;
            }
        }

        $created = 0;
        $skippedDuplicates = 0;
        $createdKeys = [];

        foreach ($albums as $album) {
            $existingLookup = array_fill_keys(
                $album->photos()
                    ->pluck('filename')
                    ->map(fn (string $filename): string => strtolower($filename))
                    ->all(),
                true
            );

            $seenInAlbum = [];
            $nextSortOrder = ((int) $album->photos()->max('sort_order')) + 1;

            foreach ($validated as $upload) {
                $key = strtolower($upload['filename']);

                if (isset($existingLookup[$key]) || isset($seenInAlbum[$key])) {
                    $skippedDuplicates++;

                    continue;
                }

                Photo::query()->create([
                    'album_id' => $album->getKey(),
                    'filename' => $upload['filename'],
                    'filepath' => $upload['path'],
                    'mime_type' => $upload['metadata']['mime_type'],
                    'filesize' => $upload['metadata']['filesize'],
                    'width' => $upload['metadata']['width'],
                    'height' => $upload['metadata']['height'],
                    'sort_order' => $nextSortOrder++,
                    'is_published' => true,
                    'is_homepage' => false,
                    'homepage_order' => 0,
                ]);

                $seenInAlbum[$key] = true;
                $createdKeys[$key] = true;
                $created++;
            }
        }

        foreach ($validated as $upload) {
            if (! isset($createdKeys[strtolower($upload['filename'])])) {
                Storage::disk('public')->delete($upload['path']);
            }
        }

        return [
            'created' => $created,
            'skippedDuplicates' => $skippedDuplicates,
            'skippedInvalid' => $skippedInvalid,
        ];
    }

    private function normalizeFilename(mixed $filename, string $path): string
    {
        $filename = is_string($filename) && filled($filename)
            ? $filename
            : basename($path);

        $filename = basename(str_replace('\\', '/', $filename));

        return filled($filename) ? $filename : basename($path);
    }

    /**
     * @return array{mime_type: string|null, filesize: int|null, width: int|null, height: int|null}|null
     */
    private function readImageMetadata(string $path): ?array
    {
        $disk = Storage::disk('public');

        try {
            if (! $disk->exists($path)) {
                return null;
            }

            $absolutePath = $disk->path($path);
            $dimensions = is_file($absolutePath) ? @getimagesize($absolutePath) : false;

            if ($dimensions === false) {
                return null;
            }

            return [
                'mime_type' => $dimensions['mime'] ?? $disk->mimeType($path),
                'filesize' => $disk->size($path),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
