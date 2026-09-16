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
        $previousMemoryLimit = null;

        try {
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

            if (! $this->gdAvailable()) {
                return null;
            }

            $this->ensureSufficientMemory((int) $imageInfo[0], (int) $imageInfo[1], $previousMemoryLimit);

            $source = match ($mime) {
                'image/jpeg' => @imagecreatefromjpeg($absolutePath),
                'image/png' => @imagecreatefrompng($absolutePath),
                'image/gif' => @imagecreatefromgif($absolutePath),
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

            imagedestroy($source);

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
        } catch (\Throwable) {
            return null;
        } finally {
            if ($previousMemoryLimit !== null) {
                @ini_set('memory_limit', $previousMemoryLimit);
            }
        }
    }

    /**
     * GD needs roughly 4 bytes per pixel to decode an image, plus additional
     * working memory for the WebP encoder. Bump the memory limit temporarily
     * for huge photos so the conversion does not run out of memory.
     */
    private function ensureSufficientMemory(int $width, int $height, ?string &$previousMemoryLimit): void
    {
        $currentLimit = $this->bytesFromIniValue(ini_get('memory_limit'));

        if ($currentLimit === -1) {
            return;
        }

        $required = memory_get_usage(true) + (int) ceil($width * $height * 4 * 1.75) + (16 * 1024 * 1024);

        if ($required <= $currentLimit) {
            return;
        }

        $previousMemoryLimit = ini_get('memory_limit');

        @ini_set('memory_limit', (string) $required);
    }

    private function bytesFromIniValue(string $value): int
    {
        $value = trim($value);

        if ($value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $amount = (int) $value;

        return match ($unit) {
            'g' => $amount * 1024 * 1024 * 1024,
            'm' => $amount * 1024 * 1024,
            'k' => $amount * 1024,
            default => $amount,
        };
    }

    private function gdAvailable(): bool
    {
        return function_exists('imagecreatefromjpeg')
            && function_exists('imagecreatefrompng')
            && function_exists('imagecreatefromgif')
            && function_exists('imagewebp');
    }
}
