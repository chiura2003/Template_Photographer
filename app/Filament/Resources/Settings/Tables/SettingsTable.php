<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Models\Setting;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('Foto'),

                TextColumn::make('photographer_name')
                    ->label('Fotografo'),

                TextColumn::make('vimeo')
                    ->label('Vimeo'),

                TextColumn::make('phone')
                    ->label('Telefono'),

                TextColumn::make('updated_at')
                    ->label('Ultima modifica')
                    ->since(),
            ])
            ->recordUrl(fn (Setting $record): string => EditSetting::getUrl(['record' => $record]))
            ->filters([])
            ->toolbarActions([])
            ->paginated(false);
    }
}
