<?php

namespace App\Filament\Resources\EventOrderResource\Pages;

use App\Filament\Resources\EventOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListEventOrders extends ListRecords
{
    protected static string $resource = EventOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
