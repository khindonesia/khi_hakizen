<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';
    
    protected static ?string $title = 'Product Variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Variant Information')
                    ->schema([
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Stock Keeping Unit - must be unique')
                            ->autocomplete(false),
                            
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                            
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                            
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Variant Image')
                            ->image()
                            ->directory('products/variants')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600'),
                            
                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Variant')
                            ->helperText('This variant will be used as the default for simple products')
                            ->default(false),
                            
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'out_of_stock' => 'Out of Stock',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Variant Attributes')
                    ->schema([
                        Forms\Components\Repeater::make('variantAttributes')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('attribute_id')
                                    ->label('Attribute')
                                    ->relationship('attribute', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive(),
                                    
                                Forms\Components\Select::make('attribute_value_id')
                                    ->label('Value')
                                    ->relationship('attributeValue', 'value', function (Builder $query, callable $get) {
                                        $attributeId = $get('attribute_id');
                                        
                                        if ($attributeId) {
                                            $query->where('attribute_id', $attributeId);
                                        }
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (callable $get) => !$get('attribute_id')),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => 
                                $state['attribute_id'] && $state['attribute_value_id'] ? 'Attribute Value' : null),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'danger',
                        'out_of_stock' => 'warning',
                        'active' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'out_of_stock' => 'Out of Stock',
                    ]),
                    
                Tables\Filters\Filter::make('in_stock')
                    ->label('In Stock')
                    ->query(fn (Builder $query) => $query->where('stock_quantity', '>', 0)),
                    
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Variant'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Variant')
                    ->modalHeading('Create New Variant')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        $data['product_id'] = $livewire->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(fn ($record) => "Edit {$record->sku}"),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('increaseStock')
                    ->label('+ Stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount to add')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->increaseStock($data['amount']);
                    })
                    ->modalHeading('Increase Stock'),
                    
                Tables\Actions\Action::make('decreaseStock')
                    ->label('- Stock')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount to remove')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1)
                            ->maxValue(fn ($record) => $record->stock_quantity),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->reduceStock($data['amount']);
                    })
                    ->modalHeading('Decrease Stock'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'out_of_stock' => 'Out of Stock',
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
                            ->title('Variant updated successfully'),
                        ),
                ]),
            ]);
    }
}