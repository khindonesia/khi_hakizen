<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class ManageCollaborationSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Collaboration';
    protected static ?string $title = 'Collaboration Page Settings';
    protected static ?int $navigationSort = 5;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('collab_page_title')
                    ->label('Page Title')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('collab_page_description')
                    ->label('Page Description')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
}
