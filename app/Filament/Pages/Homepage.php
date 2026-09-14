<?php

namespace App\Filament\Pages;

use App\Models\Photo;
use App\Services\HomepagePhotoService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class Homepage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?string $title = 'Homepage';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'homepage';

    protected string $view = 'filament.pages.homepage';

    public function getSubheading(): string|Htmlable|null
    {
        return Photo::homepageCount().'/'.Photo::HOMEPAGE_LIMIT;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Photo::query()
                    ->with('album')
                    ->where('is_homepage', true)
            )
            ->columns([
                TextColumn::make('homepage_order')
                    ->label('Ordine')
                    ->sortable(),

                ImageColumn::make('filepath')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(120)
                    ->url(fn (Photo $record): ?string => $record->image_url)
                    ->openUrlInNewTab(),

                TextColumn::make('album.title')
                    ->label('Album'),
            ])
            ->defaultSort('homepage_order')
            ->reorderable('homepage_order')
            ->reorderRecordsTriggerAction(function (Action $action, bool $isReordering): Action {
                return $action
                    ->button()
                    ->label($isReordering ? 'Fine' : 'Cambia ordine');
            })
            ->recordActions([
                Action::make('removeFromHomepage')
                    ->label('Rimuovi dalla Homepage')
                    ->icon(Heroicon::OutlinedXMark)
                    ->iconButton()
                    ->color('danger')
                    ->action(function (Photo $record): void {
                        app(HomepagePhotoService::class)->remove($record);

                        Notification::make()
                            ->title('Foto rimossa dalla Homepage')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([])
            ->toolbarActions([])
            ->paginated(false);
    }
}
