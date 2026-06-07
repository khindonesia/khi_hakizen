<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageHistorialitaSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Historialita';
    protected static ?string $title = 'Historialita Settings';
    protected static ?int $navigationSort = 10;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('news_title')
                            ->label('Page Title')
                            ->required(),
                        TextInput::make('news_chip')
                            ->label('Page Chip/Label')
                            ->required(),
                    ]),
                Textarea::make('news_subtitle')
                    ->label('Page Subtitle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
