<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Models\Attribute;
use App\Models\AttributeValue;

class ProductAttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'productAttributes';

    protected static ?string $recordTitleAttribute = 'id';
    
    protected static ?string $title = 'Product Attributes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attribute_id')
                    ->label('Attribute')
                    ->options(Attribute::where('status', 'active')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                    
                Forms\Components\TagsInput::make('attribute_values')
                    ->label('Attribute Values')
                    ->helperText('Enter all possible values for this attribute')
                    ->placeholder('Add a value and press Enter')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('attribute_values')
                    ->label('Values')
                    ->state(function ($record) {
                        return $record->attribute_values ?? [];
                    })
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Attribute')
                    ->modalHeading('Add Product Attribute')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        $data['product_id'] = $livewire->ownerRecord->id;
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Create attribute values if they don't exist
                        $attributeId = $record->attribute_id;
                        
                        foreach ($data['attribute_values'] as $value) {
                            // Check if the attribute value already exists
                            $attributeValue = AttributeValue::firstOrCreate(
                                ['attribute_id' => $attributeId, 'value' => $value],
                                ['status' => 'active']
                            );
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Product Attribute')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        // Load the current attribute values for editing
                        $data['attribute_values'] = $record->attribute_values ?? [];
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Create attribute values if they don't exist
                        $attributeId = $record->attribute_id;
                        
                        foreach ($data['attribute_values'] as $value) {
                            // Check if the attribute value already exists
                            $attributeValue = AttributeValue::firstOrCreate(
                                ['attribute_id' => $attributeId, 'value' => $value],
                                ['status' => 'active']
                            );
                        }
                        
                        // Update the record with the new values
                        $record->update([
                            'attribute_values' => $data['attribute_values'],
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}