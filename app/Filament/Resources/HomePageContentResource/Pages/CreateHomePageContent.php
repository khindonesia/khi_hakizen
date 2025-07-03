<?php

namespace App\Filament\Resources\HomePageContentResource\Pages;

use App\Filament\Resources\HomePageContentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHomePageContent extends CreateRecord
{
    protected static string $resource = HomePageContentResource::class;

    protected ?string $maxContentWidth = '7xl';

    public function getTitle(): string
    {
        return "Create Home Page Content";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
