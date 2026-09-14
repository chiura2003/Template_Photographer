<?php

namespace App\Filament\Resources\Photos;

use App\Filament\Resources\Photos\Schemas\PhotoForm;
use App\Models\Photo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PhotoForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected static bool $shouldRegisterNavigation = false;
}
