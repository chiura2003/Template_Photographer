<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('photographer_name')
                    ->required(),

                TextInput::make('phone')
                    ->tel(),

                Textarea::make('bio')
                    ->columnSpanFull(),

                FileUpload::make('profile_image')
                    ->image(),

                TextInput::make('instagram_url')
                    ->url(),

                TextInput::make('vimeo')
                    ->label('Vimeo URL')
                    ->url(),
            ]);
    }
}
