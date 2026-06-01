<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\Builder;

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
                ->dehydrateStateUsing(fn($state) => \Illuminate\Support\Facades\Hash::make($state))
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
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(url('storage/demo/default.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn(User $record): ?string => "@{$record->username}"),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

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

                Tables\Columns\ToggleColumn::make('verified')
                    ->label('Verified')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined Date')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // Mengubah posisi layout filter agar terbuka di atas konten tabel (seperti pada gambar)
            ->filtersLayout(FiltersLayout::AboveContent)

            ->filters([
                Tables\Filters\TernaryFilter::make('verified')
                    ->label('Verification Status')
                    ->placeholder('All Users')
                    ->trueLabel('Verified Only')
                    ->falseLabel('Unverified Only')
                    // PERBAIKAN DI SINI: Paksa query agar mendeteksi 0 dan null untuk status unverified
                    ->queries(
                        true: fn(Builder $query) => $query->where('verified', true),
                        false: fn(Builder $query) => $query->where(function (Builder $q) {
                            $q->where('verified', false)
                                ->orWhere('verified', 0)
                                ->orWhereNull('verified');
                        }),
                        blank: fn(Builder $query) => $query, // Jika pilih 'All Users', tidak merubah query
                    ),

                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
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
            ->striped()
            ->defaultSort('created_at', 'desc');
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
