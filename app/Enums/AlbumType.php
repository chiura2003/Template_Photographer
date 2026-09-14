<?php

namespace App\Enums;

enum AlbumType: string
{
    case Personal = 'personal';
    case Work = 'work';

    public function label(): string
    {
        return match ($this) {
            self::Personal => 'Personal',
            self::Work => 'Work',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [
                $type->value => $type->label(),
            ])
            ->toArray();
    }
}