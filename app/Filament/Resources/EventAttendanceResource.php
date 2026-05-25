<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventAttendanceResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers\EventAttendanceRelationManager;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventAttendanceResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'phosphor-check-square-duotone';
    protected static ?string $navigationGroup = 'Event Management';
    protected static ?string $navigationLabel = 'Kehadiran Event';
    protected static ?string $pluralLabel = 'Kehadiran Event';
    protected static ?string $modelLabel = 'Kehadiran';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->has('users')->withCount('users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Event')
                    ->schema([
                        Forms\Components\Placeholder::make('title')
                            ->label('Nama Event')
                            ->content(fn (Event $record): string => $record->title),
                        Forms\Components\Placeholder::make('start_datetime')
                            ->label('Waktu Mulai')
                            ->content(fn (Event $record): string => $record->start_datetime->format('d M Y H:i')),
                        Forms\Components\Placeholder::make('location')
                            ->label('Lokasi')
                            ->content(fn (Event $record): ?string => $record->location ?? '-'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Event')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Waktu Mulai')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah Pendaftar / Kehadiran')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Detail Kehadiran')
                    ->icon('heroicon-m-eye')
                    ->color('primary'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            EventAttendanceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventAttendances::route('/'),
            'edit' => Pages\EditEventAttendance::route('/{record}/edit'),
        ];
    }
}
