<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;

class ManageHomeSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'Home Page';
    protected static ?string $title = 'Home Page Settings';
    protected static ?int $navigationSort = 2;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Hero Banner')
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Hero Title')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('hero_subtitle')
                            ->label('Hero Subtitle')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('hero_button_text')
                                    ->label('Hero Button Text')
                                    ->required(),
                                FileUpload::make('hero_image')
                                    ->label('Hero Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('settings')
                                    ->visibility('public')
                                    ->maxSize(2048),
                            ]),
                    ]),
                Section::make('Sections Configuration')
                    ->schema([
                        Fieldset::make('Upcoming Events Section')
                            ->schema([
                                TextInput::make('home_events_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_events_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('home_events_view_all_text')
                                    ->label('View All Button Text')
                                    ->required(),
                                TextInput::make('home_events_register_text')
                                    ->label('Register Button Text')
                                    ->required(),
                            ])
                            ->columns(3),
                        
                        Fieldset::make('E-Book Showcase Section')
                            ->schema([
                                TextInput::make('home_library_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_library_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('home_library_explore_text')
                                    ->label('Explore Button Text')
                                    ->required(),
                                TextInput::make('home_library_read_sample_text')
                                    ->label('Read Sample Text')
                                    ->required(),
                                TextInput::make('home_library_get_ebook_text')
                                    ->label('Get E-Book Text')
                                    ->required(),
                            ])
                            ->columns(3),

                        Fieldset::make('Merchandise Section')
                            ->schema([
                                TextInput::make('home_merchandise_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_merchandise_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('home_merchandise_view_all_text')
                                    ->label('View All Button Text')
                                    ->required(),
                                TextInput::make('home_merchandise_view_product_text')
                                    ->label('View Product Button Text')
                                    ->required(),
                            ])
                            ->columns(2),

                        Fieldset::make('Historia News Section')
                            ->schema([
                                TextInput::make('home_news_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_news_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('home_news_view_all_text')
                                    ->label('View All Button Text')
                                    ->required(),
                                TextInput::make('home_news_read_more_text')
                                    ->label('Read More Text')
                                    ->required(),
                            ])
                            ->columns(2),

                        Fieldset::make('Bento Recognition & Awards Section')
                            ->schema([
                                TextInput::make('home_recognition_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_recognition_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),

                        Fieldset::make('Gabung/CTA Section')
                            ->schema([
                                TextInput::make('home_cta_title')
                                    ->label('Section Title')
                                    ->required(),
                                Textarea::make('home_cta_subtitle')
                                    ->label('Section Subtitle')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('home_cta_primary_btn_text')
                                    ->label('Primary Button Text')
                                    ->required(),
                                TextInput::make('home_cta_secondary_btn_text')
                                    ->label('Secondary Button Text')
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->statePath('data');
    }
}
