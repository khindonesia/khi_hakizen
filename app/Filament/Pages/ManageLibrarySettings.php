<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageLibrarySettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'E-Library';
    protected static ?string $title = 'E-Library Settings';
    protected static ?int $navigationSort = 6;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('library_title')
                    ->label('Page Title')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('library_subtitle')
                    ->label('Page Subtitle')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
