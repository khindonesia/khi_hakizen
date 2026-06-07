<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageAspirasiSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Aspirasi';
    protected static ?string $title = 'Aspirasi Settings';
    protected static ?int $navigationSort = 9;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('aspirasi_title')
                            ->label('Page Title')
                            ->required(),
                        TextInput::make('aspirasi_chip')
                            ->label('Page Chip/Label')
                            ->required(),
                    ]),
                Textarea::make('aspirasi_subtitle')
                    ->label('Page Subtitle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
