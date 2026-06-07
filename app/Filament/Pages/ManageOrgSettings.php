<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;

class ManageOrgSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'About & Org';
    protected static ?string $title = 'About & Organization Settings';
    protected static ?int $navigationSort = 3;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General About (Vision & Mission)')
                    ->schema([
                        Textarea::make('about_vision')
                            ->label('Vision')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('about_mission')
                            ->label('Mission')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        RichEditor::make('about_description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Organization Page Sections')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('org_page_chip')
                                    ->label('Page Chip/Badge')
                                    ->required(),
                                TextInput::make('org_page_title')
                                    ->label('Page Title')
                                    ->required(),
                                Textarea::make('org_page_subtitle')
                                    ->label('Page Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpan(3),
                            ]),
                        Fieldset::make('Our Team Section')
                            ->schema([
                                TextInput::make('org_page_team_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('org_page_team_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Achievements Section')
                            ->schema([
                                TextInput::make('org_page_achievements_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('org_page_achievements_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Milestones Section')
                            ->schema([
                                TextInput::make('org_page_milestones_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('org_page_milestones_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make('Collaboration Section')
                            ->schema([
                                TextInput::make('org_page_collab_chip')
                                    ->label('Section Chip')
                                    ->required(),
                                TextInput::make('org_page_collab_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('org_page_collab_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('org_page_collab_contact_title')
                                    ->label('Contact Section Title')
                                    ->required(),
                                TextInput::make('org_page_collab_email_label')
                                    ->label('Email Label')
                                    ->required(),
                                TextInput::make('org_page_collab_email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required(),
                                TextInput::make('org_page_collab_phone_label')
                                    ->label('Phone/WhatsApp Label')
                                    ->required(),
                                TextInput::make('org_page_collab_phone')
                                    ->label('Phone/WhatsApp Number')
                                    ->required(),
                                TextInput::make('org_page_collab_btn_text')
                                    ->label('Contact Button Text')
                                    ->required(),
                            ])
                            ->columns(3),
                    ]),
            ])
            ->statePath('data');
    }
}
