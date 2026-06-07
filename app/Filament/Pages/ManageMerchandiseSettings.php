<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageMerchandiseSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Merchandise';
    protected static ?string $title = 'Merchandise Settings';
    protected static ?int $navigationSort = 7;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('merchandise_title')
                            ->label('Page Title')
                            ->required(),
                        TextInput::make('merchandise_chip')
                            ->label('Page Chip/Label')
                            ->required(),
                    ]),
                Textarea::make('merchandise_subtitle')
                    ->label('Page Subtitle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
