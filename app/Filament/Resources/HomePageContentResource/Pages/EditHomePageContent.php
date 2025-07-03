<?php

namespace App\Filament\Resources\HomePageContentResource\Pages;

use App\Filament\Resources\HomePageContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomePageContent extends EditRecord
{
    protected static string $resource = HomePageContentResource::class;

    protected ?string $maxContentWidth = '7xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return "Edit Home Page Content";
    }
}
