<?php

namespace App\Filament\Resources\Albums\Tables;

use App\Enums\AlbumType;
use App\Models\Album;
use App\Services\AlbumPhotoUploadService;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AlbumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with('coverPhoto')
                    ->withCount([
                        'photos',
                        'photos as published_photos_count' => fn ($query) => $query->where('is_published', true),
                        'photos as homepage_photos_count' => fn ($query) => $query->where('is_homepage', true),
                    ])
            )
            ->columns([
                ImageColumn::make('coverPhoto.filepath')
                    ->label('Copertina')
                    ->disk('public')
                    ->square()
                    ->size(80),

                TextColumn::make('title')
                    ->label('Titolo')
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Anno')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (AlbumType $state) => $state->label()),

                IconColumn::make('is_published')
                    ->label('Pubblicato')
                    ->boolean(),

                TextColumn::make('published_photos_count')
                    ->label('Pubblicate')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->formatStateUsing(
                        fn (Album $record): string => "{$record->published_photos_count}/{$record->photos_count}"
                    ),

                TextColumn::make('homepage_photos_count')
                    ->label('In homepage')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->formatStateUsing(
                        fn (Album $record): string => "{$record->homepage_photos_count}/{$record->photos_count}"
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Elimina album selezionati'),
                    self::addPhotosAction(),
                    self::addFolderAction(),
                ])
                    ->iconButton(),
            ])
            ->paginated(false);
    }

    private static function addPhotosAction(): BulkAction
    {
        return BulkAction::make('addPhotos')
            ->label('Aggiungi foto')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->modalHeading('Aggiungi foto agli album selezionati')
            ->modalSubmitActionLabel('Aggiungi')
            ->modalWidth('5xl')
            ->form(function (BulkAction $action): array {
                return [
                    self::uploadField($action, directoryUpload: false),
                ];
            })
            ->action(function (BulkAction $action, array $data): void {
                self::uploadToSelectedAlbums($action, $data);
            });
    }

    private static function addFolderAction(): BulkAction
    {
        return BulkAction::make('addFolder')
            ->label('Aggiungi cartella')
            ->icon(Heroicon::OutlinedFolderOpen)
            ->color('gray')
            ->modalHeading('Aggiungi cartella agli album selezionati')
            ->modalSubmitActionLabel('Aggiungi')
            ->modalWidth('5xl')
            ->form(function (BulkAction $action): array {
                return [
                    self::uploadField($action, directoryUpload: true),
                ];
            })
            ->action(function (BulkAction $action, array $data): void {
                self::uploadToSelectedAlbums($action, $data);
            });
    }

    private static function uploadField(BulkAction $action, bool $directoryUpload = false): FileUpload
    {
        $field = FileUpload::make('photos')
            ->label('Foto')
            ->acceptedFileTypes(AlbumPhotoUploadService::IMAGE_MIME_TYPES)
            ->maxSize(AlbumPhotoUploadService::MAX_IMAGE_SIZE)
            ->maxFiles(AlbumPhotoUploadService::MAX_BULK_FILES)
            ->maxParallelUploads(2)
            ->multiple()
            ->appendFiles()
            ->reorderable()
            ->panelLayout('grid')
            ->disk('public')
            ->directory(fn (): string => 'albums/'.($action->getSelectedRecords()->first()?->slug ?? 'album'))
            ->visibility('public')
            ->storeFileNamesIn('photo_file_names')
            ->columnSpanFull();

        if ($directoryUpload) {
            $field->extraInputAttributes([
                'directory' => true,
                'webkitdirectory' => true,
            ], merge: true);
        }

        return $field;
    }

    private static function uploadToSelectedAlbums(BulkAction $action, array $data): void
    {
        $albums = $action->getSelectedRecords();

        if ($albums->isEmpty()) {
            return;
        }

        $result = app(AlbumPhotoUploadService::class)->uploadToAlbums($data, $albums);

        $details = [];

        if ($result['skippedDuplicates'] > 0) {
            $details[] = $result['skippedDuplicates'].' duplicate ignorate.';
        }

        if ($result['skippedInvalid'] > 0) {
            $details[] = $result['skippedInvalid'].' file non validi ignorati.';
        }

        Notification::make()
            ->title($result['created'] === 1 ? '1 foto caricata' : $result['created'].' foto caricate')
            ->body($details === [] ? null : implode(' ', $details))
            ->status($result['created'] > 0 ? 'success' : 'warning')
            ->send();
    }
}
