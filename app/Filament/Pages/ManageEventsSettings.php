<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageEventsSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Events';
    protected static ?string $title = 'Events Settings';
    protected static ?int $navigationSort = 8;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('events_title')
                            ->label('Page Title')
                            ->required(),
                        TextInput::make('events_chip')
                            ->label('Page Chip/Label')
                            ->required(),
                    ]),
                Textarea::make('events_subtitle')
                    ->label('Page Subtitle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
