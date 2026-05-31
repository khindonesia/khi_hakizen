<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialResource\Pages;
use App\Models\Social;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;

class SocialResource extends Resource
{
    protected static ?string $model = Social::class;

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Social Media Detail')
                    ->description('Manage your social media links and icons.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Platform Name')
                            ->placeholder('e.g., GitHub, LinkedIn')
                            ->required()
                            ->maxLength(191),

                        Forms\Components\TextInput::make('url')
                            ->label('URL Link')
                            ->placeholder('https://...')
                            ->url()
                            ->required()
                            ->maxLength(191),

                        IconPicker::make('icon')
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Platform')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('Link URL')
                    ->searchable()
                    ->icon('heroicon-m-link')
                    ->iconColor('gray')
                    ->color('primary')
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon Identifier')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListSocials::route('/'),
            'create' => Pages\CreateSocial::route('/create'),
            'edit' => Pages\EditSocial::route('/{record}/edit'),
        ];
    }
}
