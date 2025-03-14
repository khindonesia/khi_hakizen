<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Merchandise Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Attributes';

    protected static ?string $modelLabel = 'Attribute';

    protected static ?string $pluralModelLabel = 'Attributes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->heading('Attribute Information')
                    ->description('Enter the attribute details below.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Attribute Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter attribute name')
                            ->autocapitalize('words')
                            ->columnSpan(1),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->helperText('Controls visibility in frontend')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Attribute Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'danger',
                        'active' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values')
                    ->counts('values')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('productAttributes_count')
                    ->label('Used in Products')
                    ->counts('productAttributes')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('variantAttributes_count')
                    ->label('Used in Variants')
                    ->counts('variantAttributes')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                    
                Tables\Filters\Filter::make('has_values')
                    ->label('With Values')
                    ->query(fn (Builder $query): Builder => $query->whereHas('values'))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('used_in_products')
                    ->label('Used in Products')
                    ->query(fn (Builder $query): Builder => $query->whereHas('productAttributes'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                        
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->successNotification(
                            notification: \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Attributes updated successfully'),
                        ),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-adjustments-horizontal')
            ->emptyStateHeading('No Attributes Found')
            ->emptyStateDescription('Create your first attribute to define product variations.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Attribute')
                    ->url(route('filament.admin.resources.attributes.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}