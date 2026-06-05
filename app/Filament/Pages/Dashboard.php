<?php

namespace App\Filament\Pages;

use Filament\Panel;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'phosphor-house-duotone';

    public static function canAccess(): bool
    {
        return auth()->user()->can('dashboard.view');
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->pages([]);
    }
}
