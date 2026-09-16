<?php

namespace App\Filament\Widgets;

use App\Enums\AlbumType;
use App\Models\Album;
use App\Models\Photo;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PhotoStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Panoramica';

    protected ?string $pollingInterval = null;

    protected int|array|null $columns = 4;

    protected function getStats(): array
    {
        $albumTypeCounts = Album::query()
            ->selectRaw('type, count(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $totalAlbums = array_sum($albumTypeCounts->all());
        $personalAlbums = (int) ($albumTypeCounts[AlbumType::Personal->value] ?? 0);
        $workAlbums = (int) ($albumTypeCounts[AlbumType::Work->value] ?? 0);

        $photoCounts = Photo::query()
            ->selectRaw('count(*) as total, coalesce(sum(is_published), 0) as published, coalesce(sum(is_homepage), 0) as homepage')
            ->first();

        return [
            Stat::make('Album totali', $this->formatCount($totalAlbums))
                ->description("Personal: {$this->formatCount($personalAlbums)} / Work: {$this->formatCount($workAlbums)}")
                ->icon(Heroicon::OutlinedRectangleGroup)
                ->color('warning'),

            Stat::make('Foto totali', $this->formatCount((int) $photoCounts->total))
                ->icon(Heroicon::OutlinedPhoto)
                ->color('gray'),

            Stat::make('Foto pubblicate', $this->formatCount((int) $photoCounts->published))
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Foto in homepage', $this->formatCount((int) $photoCounts->homepage))
                ->icon(Heroicon::OutlinedHome)
                ->color('warning'),
        ];
    }

    private function formatCount(int $count): string
    {
        return number_format($count, 0, ',', '.');
    }
}
