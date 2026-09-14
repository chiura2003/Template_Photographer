<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        $setting = Setting::query()->latest('id')->first();

        if ($setting) {
            return [
                Action::make('editSetting')
                    ->label('Modifica')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(EditSetting::getUrl(['record' => $setting])),
            ];
        }

        return [
            CreateAction::make()
                ->label('Crea'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Informazioni personali';
    }
}