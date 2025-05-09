<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = \App\Models\Product::find($state);
                            if ($product) {
                                $set('price', $product->price);
                                $set('total_price', $product->price * 1); // Default quantity is 1
                            }
                        }
                    })
                    ->required(),
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($state) {
                            $variant = \App\Models\Variant::find($state);
                            if ($variant) {
                                $set('price', $variant->price);
                                $set('total_price', $variant->price * $get('quantity'));
                            }
                        }
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $price = $get('price');
                        $set('total_price', $price * $state);
                    }),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\TextInput::make('total_price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR'),
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): mixed {
                        // Create the order item
                        $orderItem = $livewire->ownerRecord->items()->create($data);
                        
                        // Update the order totals
                        $subtotal = $livewire->ownerRecord->items()->sum('total_price');
                        $shipping_fee = $livewire->ownerRecord->shipping_fee ?? 0;
                        
                        $livewire->ownerRecord->update([
                            'subtotal' => $subtotal,
                            'total_amount' => $subtotal + $shipping_fee,
                        ]);
                        
                        return $orderItem;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (array $data, $record, RelationManager $livewire): mixed {
                        // Update the order item
                        $record->update($data);
                        
                        // Update the order totals
                        $subtotal = $livewire->ownerRecord->items()->sum('total_price');
                        $shipping_fee = $livewire->ownerRecord->shipping_fee ?? 0;
                        
                        $livewire->ownerRecord->update([
                            'subtotal' => $subtotal,
                            'total_amount' => $subtotal + $shipping_fee,
                        ]);
                        
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->using(function ($record, RelationManager $livewire): void {
                        // Delete the order item
                        $record->delete();
                        
                        // Update the order totals
                        $subtotal = $livewire->ownerRecord->items()->sum('total_price');
                        $shipping_fee = $livewire->ownerRecord->shipping_fee ?? 0;
                        
                        $livewire->ownerRecord->update([
                            'subtotal' => $subtotal,
                            'total_amount' => $subtotal + $shipping_fee,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (RelationManager $livewire): void {
                            // Update the order totals after bulk delete
                            $subtotal = $livewire->ownerRecord->items()->sum('total_price');
                            $shipping_fee = $livewire->ownerRecord->shipping_fee ?? 0;
                            
                            $livewire->ownerRecord->update([
                                'subtotal' => $subtotal,
                                'total_amount' => $subtotal + $shipping_fee,
                            ]);
                        }),
                ]),
            ]);
    }
}