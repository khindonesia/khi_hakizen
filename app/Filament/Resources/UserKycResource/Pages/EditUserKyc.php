<?php

namespace App\Filament\Resources\UserKycResource\Pages;

use App\Filament\Resources\UserKycResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserKyc extends EditRecord
{
    protected static string $resource = UserKycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
