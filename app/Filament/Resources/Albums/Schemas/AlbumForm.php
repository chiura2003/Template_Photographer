<?php

namespace App\Filament\Resources\Albums\Schemas;

use App\Enums\AlbumType;
use App\Models\Album;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titolo')
                    ->required()
                    ->autocomplete(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                    ->maxLength(255)
                    ->rules([
                        static function (string $attribute, mixed $value, Closure $fail): void {
                            $slug = Str::slug((string) $value);

                            $record = request()->route('record');

                            $query = Album::query()->where('slug', $slug);

                            if ($record instanceof Album) {
                                $query->whereKeyNot($record->getKey());
                            } elseif (is_string($record) && $record !== '') {
                                $query->where('slug', '!=', $record);
                            }

                            if ($query->exists()) {
                                $fail('Esiste già un album con questo nome.');
                            }
                        },
                    ]),

                Select::make('year')
                    ->label('Anno')
                    ->options(
                        collect(range(now()->year + 5, 2010))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),
                

                ToggleButtons::make('type')
                    ->label('Pagina')
                    ->options(AlbumType::options())
                    ->icons([
                        AlbumType::Personal->value => 'heroicon-o-user',
                        AlbumType::Work->value => 'heroicon-o-briefcase',
                    ])
                    ->columns(2)
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