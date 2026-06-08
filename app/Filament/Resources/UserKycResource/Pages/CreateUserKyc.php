<?php

namespace App\Filament\Resources\UserKycResource\Pages;

use App\Filament\Resources\UserKycResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserKyc extends CreateRecord
{
    protected static string $resource = UserKycResource::class;
}
