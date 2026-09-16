<?php

namespace App\Filament\Resources\Albums\RelationManagers;

use App\Filament\Resources\Albums\Pages\EditAlbum;
use App\Models\Photo;
use App\Services\HomepagePhotoService;
use App\Services\WebpConverter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PhotosRelationManager extends RelationManager
{
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const MAX_IMAGE_SIZE = 51200;

    private const MAX_BULK_FILES = 300;

    protected static string $relationship = 'photos';

    protected ?bool $homepageLimitReached = null;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === EditAlbum::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('filepath')
                    ->label('Foto')
                    ->image()
                    ->acceptedFileTypes(self::IMAGE_MIME_TYPES)
                    ->maxSize(self::MAX_IMAGE_SIZE)
                    ->imageEditor()
                    ->disk('public')
                    ->directory(
                        fn (): string => 'albums/'.$this->getOwnerRecord()->slug
                    )
                    ->visibility('public')
                    ->preventFilePathTampering()
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('filename')
            ->heading('')
            ->columns([
                ImageColumn::make('filepath')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(120),

                ToggleColumn::make('is_published')
                    ->label('Pubblicata'),

                ToggleColumn::make('is_homepage')
                    ->label('Homepage')
                    ->disabled(fn (Photo $record): bool => ! $record->is_homepage && $this->isHomepageLimitReached())
                    ->tooltip(fn (Photo $record): ?string => (! $record->is_homepage && $this->isHomepageLimitReached())
                        ? Photo::homepageLimitMessage()
                        : null)
                    ->updateStateUsing(fn (Photo $record, bool $state): bool => $this->updateHomepageState($record, $state)),

                ToggleColumn::make('is_cover')
                    ->label('Copertina')
                    ->state(fn (Photo $record): bool => $this->isCover($record))
                    ->updateStateUsing(fn (Photo $record, bool $state): bool => $this->updateCoverState($record, $state)),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                $this->bulkUploadAction(),
                $this->folderUploadAction(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Elimina foto')
                    ->icon(Heroicon::OutlinedXMark)
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])
                    ->iconButton(),
            ])
            ->paginated(false);
    }

    private function bulkUploadAction(): Action
    {
        return Action::make('uploadPhotos')
            ->label('Carica foto')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->modalHeading('Carica foto')
            ->modalSubmitActionLabel('Carica')
            ->modalWidth('5xl')
            ->form([
                $this->bulkUploadField(),
            ])
            ->action(function (array $data): void {
                $this->createUploadedPhotos($data);
            });
    }

    private function folderUploadAction(): Action
    {
        return Action::make('uploadFolder')
            ->label('Carica cartella')
            ->icon(Heroicon::OutlinedFolderOpen)
            ->color('gray')
            ->modalHeading('Carica cartella')
            ->modalSubmitActionLabel('Carica')
            ->modalWidth('5xl')
            ->form([
                $this->bulkUploadField(directoryUpload: true),
            ])
            ->action(function (array $data): void {
                $this->createUploadedPhotos($data);
            });
    }

    private function bulkUploadField(bool $directoryUpload = false): FileUpload
    {
        $field = FileUpload::make('photos')
            ->label('Foto')
            ->acceptedFileTypes(self::IMAGE_MIME_TYPES)
            ->maxSize(self::MAX_IMAGE_SIZE)
            ->maxFiles(self::MAX_BULK_FILES)
            ->maxParallelUploads(2)
            ->multiple()
            ->appendFiles()
            ->reorderable()
            ->panelLayout('grid')
            ->disk('public')
            ->directory(fn (): string => 'albums/'.$this->getOwnerRecord()->slug)
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

    private function createUploadedPhotos(array $data): void
    {
        $uploadedPaths = $data['photos'] ?? [];
        $uploadedFileNames = $data['photo_file_names'] ?? [];

        if (! is_array($uploadedPaths)) {
            $uploadedPaths = [$uploadedPaths];
        }

        if (! is_array($uploadedFileNames)) {
            $uploadedFileNames = [];
        }

        $uploads = [];

        foreach ($uploadedPaths as $key => $path) {
            if (! is_string($path) || blank($path)) {
                continue;
            }

            $uploads[] = [
                'path' => $path,
                'filename' => $this->normalizeFilename($uploadedFileNames[$key] ?? null, $path),
            ];
        }

        usort($uploads, fn (array $first, array $second): int => strnatcasecmp($first['filename'], $second['filename']));

        $ownerRecord = $this->getOwnerRecord();
        $existingFilenames = $ownerRecord->photos()
            ->pluck('filename')
            ->map(fn (string $filename): string => strtolower($filename))
            ->all();

        $existingLookup = array_fill_keys($existingFilenames, true);
        $seenInUpload = [];
        $nextSortOrder = ((int) $ownerRecord->photos()->max('sort_order')) + 1;
        $created = 0;
        $skippedDuplicates = 0;
        $skippedInvalid = 0;

        foreach ($uploads as $upload) {
            try {
                if (str_starts_with(basename($upload['path']), '._')) {
                    Storage::disk('public')->delete($upload['path']);
                    $skippedInvalid++;

                    continue;
                }

                $filenameKey = strtolower($upload['filename']);

                if (isset($existingLookup[$filenameKey]) || isset($seenInUpload[$filenameKey])) {
                    Storage::disk('public')->delete($upload['path']);
                    $skippedDuplicates++;

                    continue;
                }

                $metadata = $this->readImageMetadata($upload['path']);

                if ($metadata === null) {
                    Storage::disk('public')->delete($upload['path']);
                    $skippedInvalid++;

                    continue;
                }

                $converted = app(WebpConverter::class)->convert($upload['path']);

                if ($converted !== null) {
                    $upload['path'] = $converted['path'];
                    $upload['filename'] = pathinfo($converted['path'], PATHINFO_BASENAME);
                    $metadata['mime_type'] = $converted['mime_type'];
                    $metadata['filesize'] = $converted['filesize'];
                }

                Photo::query()->create([
                    'album_id' => $ownerRecord->getKey(),
                    'filename' => $upload['filename'],
                    'filepath' => $upload['path'],
                    'mime_type' => $metadata['mime_type'],
                    'filesize' => $metadata['filesize'],
                    'width' => $metadata['width'],
                    'height' => $metadata['height'],
                    'sort_order' => $nextSortOrder++,
                    'is_published' => true,
                    'is_homepage' => false,
                    'homepage_order' => 0,
                ]);

                $seenInUpload[$filenameKey] = true;
                $created++;
            } catch (\Throwable) {
                Storage::disk('public')->delete($upload['path']);
                $skippedInvalid++;
            }
        }

        $this->sendUploadNotification($created, $skippedDuplicates, $skippedInvalid);
    }

    private function normalizeFilename(mixed $filename, string $path): string
    {
        $filename = is_string($filename) && filled($filename)
            ? $filename
            : basename($path);

        $filename = basename(str_replace('\\', '/', $filename));

        return filled($filename) ? $filename : basename($path);
    }

    /**
     * @return array{mime_type: string|null, filesize: int|null, width: int|null, height: int|null}|null
     */
    private function readImageMetadata(string $path): ?array
    {
        $disk = Storage::disk('public');

        try {
            if (! $disk->exists($path)) {
                return null;
            }

            $absolutePath = $disk->path($path);
            $dimensions = is_file($absolutePath) ? @getimagesize($absolutePath) : false;

            if ($dimensions === false) {
                return null;
            }

            return [
                'mime_type' => $dimensions['mime'] ?? $disk->mimeType($path),
                'filesize' => $disk->size($path),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function sendUploadNotification(int $created, int $skippedDuplicates, int $skippedInvalid): void
    {
        $details = [];

        if ($skippedDuplicates > 0) {
            $details[] = $skippedDuplicates.' duplicate ignorate.';
        }

        if ($skippedInvalid > 0) {
            $details[] = $skippedInvalid.' file non validi ignorati.';
        }

        Notification::make()
            ->title($created === 1 ? '1 foto caricata' : $created.' foto caricate')
            ->body($details === [] ? null : implode(' ', $details))
            ->status($created > 0 ? 'success' : 'warning')
            ->send();
    }

    private function updateHomepageState(Photo $photo, bool $state): bool
    {
        try {
            app(HomepagePhotoService::class)->toggle($photo, $state);

            $this->homepageLimitReached = null;

            return $state;
        } catch (ValidationException $exception) {
            Notification::make()
                ->title($exception->errors()['is_homepage'][0] ?? Photo::homepageLimitMessage())
                ->danger()
                ->send();

            $this->homepageLimitReached = null;

            return false;
        }
    }

    private function isHomepageLimitReached(): bool
    {
        return $this->homepageLimitReached ??= Photo::homepageCount() >= Photo::HOMEPAGE_LIMIT;
    }

    private function isCover(Photo $photo): bool
    {
        return $this->getOwnerRecord()->cover_photo_id === $photo->getKey();
    }

    private function updateCoverState(Photo $photo, bool $state): bool
    {
        $album = $this->getOwnerRecord();

        $album->forceFill([
            'cover_photo_id' => $state ? $photo->getKey() : null,
        ])->save();

        return $state;
    }
}
