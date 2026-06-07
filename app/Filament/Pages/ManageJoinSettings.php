<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;

class ManageJoinSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Join Page';
    protected static ?string $title = 'Join Page Settings';
    protected static ?int $navigationSort = 4;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextInput::make('join_page_chip')
                            ->label('Page Chip/Badge')
                            ->required(),
                        TextInput::make('join_page_title')
                            ->label('Page Title')
                            ->required(),
                        TextInput::make('join_page_image')
                            ->label('Showcase Image URL')
                            ->required(),
                        Textarea::make('join_page_subtitle')
                            ->label('Page Subtitle')
                            ->rows(2)
                            ->required()
                            ->columnSpan(3),
                    ]),
                Fieldset::make('Privileges List Configuration')
                    ->schema([
                        TextInput::make('join_page_privileges_title')
                            ->label('Privileges Title')
                            ->required()
                            ->columnSpanFull(),
                        
                        // Privilege 1
                        TextInput::make('join_page_privilege_1_title')
                            ->label('Privilege 1 Title')
                            ->required(),
                        TextInput::make('join_page_privilege_1_desc')
                            ->label('Privilege 1 Description')
                            ->required(),

                        // Privilege 2
                        TextInput::make('join_page_privilege_2_title')
                            ->label('Privilege 2 Title')
                            ->required(),
                        TextInput::make('join_page_privilege_2_desc')
                            ->label('Privilege 2 Description')
                            ->required(),

                        // Privilege 3
                        TextInput::make('join_page_privilege_3_title')
                            ->label('Privilege 3 Title')
                            ->required(),
                        TextInput::make('join_page_privilege_3_desc')
                            ->label('Privilege 3 Description')
                            ->required(),

                        // Privilege 4
                        TextInput::make('join_page_privilege_4_title')
                            ->label('Privilege 4 Title')
                            ->required(),
                        TextInput::make('join_page_privilege_4_desc')
                            ->label('Privilege 4 Description')
                            ->required(),
                    ])
                    ->columns(2),
                Fieldset::make('Register Form Configuration')
                    ->schema([
                        TextInput::make('join_page_form_title')
                            ->label('Form Title')
                            ->required(),
                        TextInput::make('join_page_form_subtitle')
                            ->label('Form Subtitle')
                            ->required(),
                        TextInput::make('join_page_form_btn_text')
                            ->label('Submit Button Text')
                            ->required(),
                        TextInput::make('join_page_form_footer')
                            ->label('Form Footer Text')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }
}
