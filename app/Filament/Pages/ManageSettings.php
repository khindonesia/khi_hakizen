<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;

class ManageSettings extends BaseManageSettings
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Site Settings';
    protected static ?string $navigationLabel = 'General';
    protected static ?string $title = 'General Settings';
    protected static ?int $navigationSort = 1;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings Tabs')
                    ->tabs([
                        // Tab: General Site
                        Tabs\Tab::make('General')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                TextInput::make('site.title')
                                    ->label('Site Title')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('site.description')
                                    ->label('Site Description')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('site.google_analytics_tracking_id')
                                    ->label('Google Analytics Tracking ID')
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('site_logo')
                                            ->label('Site Logo')
                                            ->image()
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->maxSize(2048),
                                        FileUpload::make('site_favicon')
                                            ->label('Site Favicon')
                                            ->image()
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->maxSize(1024),
                                    ]),
                            ]),

                        // Tab: Header & Footer
                        Tabs\Tab::make('Header & Footer')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                TextInput::make('header_tagline')
                                    ->label('Header Tagline')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('footer_address')
                                    ->label('Footer Address')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('footer_copyright')
                                            ->label('Footer Copyright')
                                            ->required(),
                                        TextInput::make('footer_contact_phone')
                                            ->label('Footer Contact Phone')
                                            ->required(),
                                        TextInput::make('footer_contact_email')
                                            ->label('Footer Contact Email')
                                            ->email()
                                            ->required(),
                                    ]),
                            ]),

                        // Tab: Social Media
                        Tabs\Tab::make('Social Media')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Repeater::make('site_social_links')
                                    ->label('Social Media Links')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Social Media Name')
                                            ->required(),
                                        TextInput::make('url')
                                            ->label('URL')
                                            ->url()
                                            ->required(),
                                        FileUpload::make('logo')
                                            ->label('Custom Logo / Icon')
                                            ->image()
                                            ->disk('public')
                                            ->directory('settings')
                                            ->visibility('public')
                                            ->maxSize(1024),
                                    ])
                                    ->createItemButtonLabel('Add Social Media Link')
                                    ->columns(3)
                                    ->columnSpanFull(),
                            ]),

                        // Tab: Legal
                        Tabs\Tab::make('Legal')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                RichEditor::make('terms_of_service')
                                    ->label('Terms of Service')
                                    ->required()
                                    ->columnSpanFull(),
                                RichEditor::make('privacy_policy')
                                    ->label('Privacy Policy')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }
}
