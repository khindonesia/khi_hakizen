<?php

namespace App\Filament\Resources\UserKycResource\Pages;

use App\Filament\Resources\UserKycResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserKycs extends ListRecords
{
    protected static string $resource = UserKycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
