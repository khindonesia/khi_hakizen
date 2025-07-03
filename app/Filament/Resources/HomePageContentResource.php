<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageContentResource\Pages;
use App\Filament\Resources\HomePageContentResource\RelationManagers;
use App\Models\HomePageContent;
use App\Models\HomeAchievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HomePageContentResource extends Resource
{
    protected static ?string $model = HomePageContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $modelLabel = 'Home Page Content';

    protected static ?string $pluralModelLabel = 'Home Page Contents';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Home Page Content')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Hero Section')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Forms\Components\Section::make('Hero Banner')
                                    ->description('Configure the main hero section of your homepage')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_title')
                                            ->label('Hero Title')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Enter compelling hero title')
                                            ->columnSpan(2),

                                        Forms\Components\Textarea::make('hero_subtitle')
                                            ->label('Hero Subtitle')
                                            ->rows(4)
                                            ->placeholder('Enter supporting subtitle')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('hero_button_text')
                                            ->label('Call-to-Action Button Text')
                                            ->maxLength(100)
                                            ->placeholder('e.g., Get Started, Learn More'),

                                        Forms\Components\FileUpload::make('hero_image')
                                            ->label('Hero Image')
                                            ->image()
                                            ->directory('hero-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Organization Info')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make('Organization Details')
                                    ->description('Basic information about your organization')
                                    ->schema([
                                        Forms\Components\TextInput::make('org_name')
                                            ->label('Organization Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Enter organization full name')
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('org_acronym')
                                            ->label('Organization Acronym')
                                            ->maxLength(20)
                                            ->placeholder('e.g., ABC, XYZ Corp'),

                                        Forms\Components\Textarea::make('org_description')
                                            ->label('Organization Description')
                                            ->rows(5)
                                            ->placeholder('Describe your organization mission and vision')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Leader Profile')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Forms\Components\Section::make('Leader Profile')
                                    ->description('Information about the organization leader')
                                    ->schema([
                                        Forms\Components\TextInput::make('leader_name')
                                            ->label('Leader Name')
                                            ->maxLength(255)
                                            ->placeholder('Enter leader full name')
                                            ->columnSpan(2),

                                        Forms\Components\TextInput::make('leader_position')
                                            ->label('Leader Position')
                                            ->maxLength(255)
                                            ->placeholder('e.g., CEO, President, Director'),

                                        Forms\Components\FileUpload::make('leader_image')
                                            ->label('Leader Photo')
                                            ->image()
                                            ->directory('leader-images')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),

                                        Forms\Components\Textarea::make('leader_bio')
                                            ->label('Leader Biography')
                                            ->rows(6)
                                            ->placeholder('Enter leader background and achievements')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Achievements')
                            ->icon('heroicon-o-trophy')
                            ->schema([
                                Forms\Components\Section::make('Organization Achievements')
                                    ->description('Manage your organization achievements and milestones')
                                    ->schema([
                                        Forms\Components\Repeater::make('achievements')
                                            ->relationship('achievements')
                                            ->schema([
                                                Forms\Components\TextInput::make('achievement_title')
                                                    ->label('Achievement Title')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Enter achievement title')
                                                    ->columnSpan(2),



                                                Forms\Components\TextInput::make('display_order')
                                                    ->label('Display Order')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->helperText('Lower numbers appear first'),

                                            ])
                                            ->columns(2)
                                            ->defaultItems(1)
                                            ->addActionLabel('Add New Achievement')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['achievement_title'] ?? 'New Achievement')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->activeTab(1)
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')
                    ->label('Hero Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('org_name')
                    ->label('Organization')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('leader_name')
                    ->label('Leader')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not set'),

                Tables\Columns\TextColumn::make('leader_position')
                    ->label('Position')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('Not set'),

                Tables\Columns\TextColumn::make('achievements_count')
                    ->label('Achievements')
                    ->counts('achievements')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_leader')
                    ->label('Has Leader Info')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('leader_name')),

                Tables\Filters\Filter::make('has_achievements')
                    ->label('Has Achievements')
                    ->query(fn(Builder $query): Builder => $query->whereHas('achievements')),

                Tables\Filters\Filter::make('complete_profile')
                    ->label('Complete Profile')
                    ->query(fn(Builder $query): Builder => $query
                        ->whereNotNull('hero_title')
                        ->whereNotNull('org_name')
                        ->whereNotNull('leader_name')
                        ->whereHas('achievements')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePageContents::route('/'),
            'create' => Pages\CreateHomePageContent::route('/create'),
            'edit' => Pages\EditHomePageContent::route('/{record}/edit'),
        ];
    }
}
