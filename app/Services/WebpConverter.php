<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class WebpConverter
{
    private const QUALITY = 82;

    /**
     * Convert an image to WebP format.
     *
     * @return array{path: string, mime_type: string, filesize: int}|null
     */
    public function convert(string $storagePath): ?array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($storagePath)) {
            return null;
        }

        $absolutePath = $disk->path($storagePath);
        $imageInfo = @getimagesize($absolutePath);

        if ($imageInfo === false) {
            return null;
        }

        $mime = $imageInfo['mime'];

        if ($mime === 'image/webp') {
            return [
                'path' => $storagePath,
                'mime_type' => $mime,
                'filesize' => $disk->size($storagePath),
            ];
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => imagecreatefrompng($absolutePath),
            'image/gif' => imagecreatefromgif($absolutePath),
            default => null,
        };

        if ($source === null) {
            return null;
        }

        $dir = dirname($storagePath);
        $newFilename = pathinfo($storagePath, PATHINFO_FILENAME).'.webp';
        $newPath = $dir === '.' ? $newFilename : $dir.'/'.$newFilename;

        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagesavealpha($source, true);
            imagealphablending($source, false);
        }

        $result = imagewebp($source, $disk->path($newPath), self::QUALITY);

        if (! $result) {
            return null;
        }

        if ($newPath !== $storagePath) {
            $disk->delete($storagePath);
        }

        return [
            'path' => $newPath,
            'mime_type' => 'image/webp',
            'filesize' => $disk->size($newPath),
        ];
    }
}
