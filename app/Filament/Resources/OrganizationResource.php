<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Filament\Resources\OrganizationResource\RelationManagers;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'phosphor-buildings-duotone';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->directory('organizations')
                    ->nullable(),
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('facebook_url')
                    ->prefixAction(
                        Forms\Components\Actions\Action::make('facebook')
                            ->icon('heroicon-m-globe-alt')
                            ->tooltip('URL akan otomatis dimulai dengan https://')
                    )
                    ->maxLength(191)
                    ->nullable()
                    ->helperText('Cukup masukkan domain tanpa https://')
                    ->beforeStateDehydrated(function ($state, callable $get, callable $set) {
                        if ($state) {
                            // Hapus https:// jika sudah ada
                            $cleanUrl = preg_replace('/^https?:\/\//', '', $state);
                            // Tambahkan https:// di depan
                            $set('facebook_url', 'https://' . $cleanUrl);
                        }
                    }),
                Forms\Components\TextInput::make('instagram_url')
                    ->prefixAction(
                        Forms\Components\Actions\Action::make('instagram')
                            ->icon('heroicon-m-globe-alt')
                            ->tooltip('URL akan otomatis dimulai dengan https://')
                    )
                    ->maxLength(191)
                    ->nullable()
                    ->helperText('Cukup masukkan domain tanpa https://')
                    ->beforeStateDehydrated(function ($state, callable $get, callable $set) {
                        if ($state) {
                            // Hapus https:// jika sudah ada
                            $cleanUrl = preg_replace('/^https?:\/\//', '', $state);
                            // Tambahkan https:// di depan
                            $set('instagram_url', 'https://' . $cleanUrl);
                        }
                    }),
                Forms\Components\TextInput::make('linkedin_url')
                    ->prefixAction(
                        Forms\Components\Actions\Action::make('linkedin')
                            ->icon('heroicon-m-globe-alt')
                            ->tooltip('URL akan otomatis dimulai dengan https://')
                    )
                    ->maxLength(191)
                    ->nullable()
                    ->helperText('Cukup masukkan domain tanpa https://')
                    ->beforeStateDehydrated(function ($state, callable $get, callable $set) {
                        if ($state) {
                            // Hapus https:// jika sudah ada
                            $cleanUrl = preg_replace('/^https?:\/\//', '', $state);
                            // Tambahkan https:// di depan
                            $set('linkedin_url', 'https://' . $cleanUrl);
                        }
                    }),
                Forms\Components\TextInput::make('twitter_url')
                    ->prefixAction(
                        Forms\Components\Actions\Action::make('twitter')
                            ->icon('heroicon-m-globe-alt')
                            ->tooltip('URL akan otomatis dimulai dengan https://')
                    )
                    ->maxLength(191)
                    ->nullable()
                    ->helperText('Cukup masukkan domain tanpa https://')
                    ->beforeStateDehydrated(function ($state, callable $get, callable $set) {
                        if ($state) {
                            // Hapus https:// jika sudah ada
                            $cleanUrl = preg_replace('/^https?:\/\//', '', $state);
                            // Tambahkan https:// di depan
                            $set('twitter_url', 'https://' . $cleanUrl);
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('avatar'),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}