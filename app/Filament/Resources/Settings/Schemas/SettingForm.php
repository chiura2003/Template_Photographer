<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('photographer_name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                TextInput::make('phone')
                    ->tel(),

                Textarea::make('bio')
                    ->columnSpanFull(),

                FileUpload::make('profile_image')
                    ->image(),

                TextInput::make('instagram_url')
                    ->url(),

                TextInput::make('facebook_url')
                    ->url(),

                TextInput::make('website_url')
                    ->url(),
            ]);
    }
}