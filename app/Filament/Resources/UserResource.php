<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'phosphor-users-duotone';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(191),
            Forms\Components\TextInput::make('username')
                ->required()
                ->maxLength(191),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(191),
            Forms\Components\FileUpload::make('avatar')
                ->required()
                ->image(),
            Forms\Components\DateTimePicker::make('email_verified_at'),
            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn($state) => Hash::make($state))
                ->dehydrated(fn($state) => filled($state))
                ->required(fn(string $context): bool => $context === 'create'),
            Forms\Components\Select::make('roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->searchable()
                ->required(),
            Forms\Components\DateTimePicker::make('trial_ends_at'),
            Forms\Components\TextInput::make('verification_code')
                ->maxLength(191),
            Forms\Components\Toggle::make('verified')
                ->label('Verified')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Avatar Column with circular styling and default fallback
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(url('storage/demo/default.png')),

                // 2. Name Column with Username displayed as a description below it
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->copyable() // Click to copy feature
                    ->description(fn(User $record): ?string => "@{$record->username}"),

                // 3. Email Column with quick copy support
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // 4. Roles Account Type displayed as BADGES with dynamic coloring
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'panel_user' => 'warning',
                        'writer' => 'info',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                // 5. Verification Toggle directly inside the table row
                Tables\Columns\ToggleColumn::make('verified')
                    ->label('Verified')
                    ->sortable(),

                // 6. Creation Date (Hidden by default, can be enabled via Column Toggle)
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined Date')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter 1: Ternary filter for verification status (True / False / All)
                Tables\Filters\TernaryFilter::make('verified')
                    ->label('Verification Status')
                    ->placeholder('All Users')
                    ->trueLabel('Verified Only')
                    ->falseLabel('Unverified Only'),

                // Filter 2: Multi-select filter for Roles with data preloading
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                // Grouping actions into a clean dropdown menu
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateDescription('Start by creating a new user to populate this list.')
            ->striped() // Alternating row backgrounds for better readability
            ->defaultSort('created_at', 'desc'); // Always show newest users first
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
