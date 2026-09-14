<?php

namespace App\Filament\Resources\Albums\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Enums\AlbumType;

class AlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titolo')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                    ->maxLength(255),

            

                Select::make('year')
                    ->label('Anno')
                    ->options(
                        collect(range(now()->year + 5, 2010))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),
                

                Select::make('type')
                    ->label('Tipo')
                    ->options(AlbumType::options())
                    ->default(AlbumType::Personal->value)
                    ->required(),

                Textarea::make('description')
    ->label('Descrizione')
    ->rows(1),

                Toggle::make('is_published')
                    ->label('Pubblica album')
                    ->default(true),
            ]);
    }
}