<?php

namespace App\Filament\Resources\EventAttendanceResource\Pages;

use App\Filament\Resources\EventAttendanceResource;
use Filament\Resources\Pages\EditRecord;

class EditEventAttendance extends EditRecord
{
    protected static string $resource = EventAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
