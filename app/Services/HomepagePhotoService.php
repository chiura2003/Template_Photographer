<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HomepagePhotoService
{
    public function add(Photo $photo): Photo
    {
        return DB::transaction(function () use ($photo): Photo {
            $photo = Photo::query()
                ->whereKey($photo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($photo->is_homepage) {
                return $photo;
            }

            if (! $photo->is_published || ! $photo->album()->where('is_published', true)->exists()) {
                throw ValidationException::withMessages([
                    'is_homepage' => 'Puoi aggiungere alla Homepage solo foto pubblicate di album pubblicati.',
                ]);
            }

            $homepageIds = Photo::query()
                ->where('is_homepage', true)
                ->lockForUpdate()
                ->pluck('id');

            if ($homepageIds->count() >= Photo::HOMEPAGE_LIMIT) {
                throw ValidationException::withMessages([
                    'is_homepage' => Photo::homepageLimitMessage(),
                ]);
            }

            $photo->forceFill([
                'is_homepage' => true,
                'homepage_order' => $homepageIds->count() + 1,
            ])->save();

            $this->syncOrder();

            return $photo->refresh();
        });
    }

    public function remove(Photo $photo): Photo
    {
        return DB::transaction(function () use ($photo): Photo {
            $photo = Photo::query()
                ->whereKey($photo->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $photo->is_homepage) {
                return $photo;
            }

            $photo->forceFill([
                'is_homepage' => false,
                'homepage_order' => 0,
            ])->save();

            $this->syncOrder();

            return $photo->refresh();
        });
    }

    public function toggle(Photo $photo, bool $isHomepage): Photo
    {
        return $isHomepage
            ? $this->add($photo)
            : $this->remove($photo);
    }

    /**
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds): void {
            $orderedIds = collect($orderedIds)
                ->map(fn (int|string $id): int => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $validOrderedIds = Photo::query()
                ->where('is_homepage', true)
                ->whereIn('id', $orderedIds)
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            $validOrderedIds = $orderedIds
                ->filter(fn (int $id): bool => in_array($id, $validOrderedIds, true))
                ->values();

            $remainingIds = Photo::query()
                ->where('is_homepage', true)
                ->whereNotIn('id', $validOrderedIds)
                ->orderBy('homepage_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            $finalOrder = $validOrderedIds
                ->concat($remainingIds)
                ->values();

            foreach ($finalOrder as $index => $photoId) {
                Photo::query()
                    ->whereKey($photoId)
                    ->update(['homepage_order' => $index + 1]);
            }

            Photo::query()
                ->where('is_homepage', false)
                ->where('homepage_order', '!=', 0)
                ->update(['homepage_order' => 0]);
        });
    }

    public function syncOrder(): void
    {
        DB::transaction(function (): void {
            $photos = Photo::query()
                ->where('is_homepage', true)
                ->orderByRaw('case when homepage_order is null or homepage_order < 1 then 1 else 0 end')
                ->orderBy('homepage_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($photos as $index => $photo) {
                $expectedOrder = $index + 1;

                if ((int) $photo->homepage_order === $expectedOrder) {
                    continue;
                }

                $photo->updateQuietly([
                    'homepage_order' => $expectedOrder,
                ]);
            }

            Photo::query()
                ->where('is_homepage', false)
                ->where('homepage_order', '!=', 0)
                ->update(['homepage_order' => 0]);
        });
    }
}
