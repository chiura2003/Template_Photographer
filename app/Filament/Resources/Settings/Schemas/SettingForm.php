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
            ->columns(2)
            ->components([
                TextInput::make('photographer_name')
                    ->label('Nome Fotografo'),

                TextInput::make('email')
                    ->label('Email')
                    ->rules(['email']),

                TextInput::make('instagram_url')
                    ->url(),

                TextInput::make('vimeo')
                    ->label('Vimeo URL')
                    ->url(),

                TextInput::make('location')
                    ->label('Località'),

                TextInput::make('phone')
                    ->label('Telefono')
                    ->telRegex('/^[0-9+()\-. ]{5,20}$/')
                    ->tel(),

                Textarea::make('bio')
                    ->columnSpanFull()
                    ->rows(10),

                FileUpload::make('profile_image')
                    ->label('Foto profilo')
                    ->image()
                    ->columnSpanFull(),
            ]);
    }
}