<?php

namespace App\Filament\Resources\EventOrderResource\Pages;

use App\Filament\Resources\EventOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditEventOrder extends EditRecord
{
    protected static string $resource = EventOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
