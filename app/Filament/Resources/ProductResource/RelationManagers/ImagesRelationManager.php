<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $recordTitleAttribute = 'image_url';
    
    protected static ?string $title = 'Product Images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_url')
                    ->label('Image')
                    ->image()
                    ->required()
                    ->directory('products/images')
                    ->disk('public')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('1200')
                    ->imageResizeTargetHeight('1200')
                    ->columnSpanFull()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif']),
                    
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Lower numbers appear first'),
                    
                Forms\Components\Toggle::make('is_primary')
                    ->label('Primary Image')
                    ->helperText('This image will be used as the main product image')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->square()
                    ->size(150),
                    
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                    
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Images'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Images')
                    ->modalHeading('Upload Product Images')
                    ->using(function (array $data, RelationManager $livewire): Model {
                        // Make sure product_id is set
                        $data['product_id'] = $livewire->ownerRecord->id;
                        
                        // Create record
                        $record = $livewire->getRelationship()->create($data);
                        
                        // Handle primary image logic after creating
                        if ($data['is_primary']) {
                            $livewire->ownerRecord->images()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        }
                        
                        // Set as primary if it's the first image
                        $totalImages = $livewire->ownerRecord->images()->count();
                        if ($totalImages === 1) {
                            $record->update(['is_primary' => true]);
                        }
                        
                        return $record;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Edit Image')
                    ->using(function (Model $record, array $data): Model {
                        // Update record
                        $record->update($data);
                        
                        // Handle primary image logic
                        if ($data['is_primary']) {
                            $record->product->images()
                                ->where('id', '!=', $record->id)
                                ->update(['is_primary' => false]);
                        } else {
                            // Ensure at least one image is primary
                            $primaryExists = $record->product->images()
                                ->where('is_primary', true)
                                ->exists();
                            
                            if (!$primaryExists) {
                                $record->update(['is_primary' => true]);
                                
                                // Show notification that we kept it as primary
                                Notification::make()
                                    ->warning()
                                    ->title('At least one image must be primary')
                                    ->body('This image remains set as primary since no other primary image exists.')
                                    ->send();
                            }
                        }
                        
                        return $record;
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        // If this was the primary image, set another one as primary
                        if ($record->is_primary) {
                            $newPrimary = $record->product->images()
                                ->where('id', '!=', $record->id)
                                ->first();
                            
                            if ($newPrimary) {
                                $newPrimary->update(['is_primary' => true]);
                            }
                        }
                    }),
                    
                Tables\Actions\Action::make('setPrimary')
                    ->label('Set as Primary')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (Model $record) {
                        // Update all other images first
                        $record->product->images()
                            ->where('id', '!=', $record->id)
                            ->update(['is_primary' => false]);
                        
                        // Set this one as primary
                        $record->update(['is_primary' => true]);
                    })
                    ->visible(fn (Model $record) => !$record->is_primary)
                    ->successNotification(
                        notification: Notification::make()
                            ->success()
                            ->title('Primary image updated successfully')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            // Check if any primary images are being deleted
                            $containsPrimary = $records->contains('is_primary', true);
                            
                            if ($containsPrimary) {
                                // Find an image that's not being deleted to make primary
                                $record = $records->first();
                                $recordIds = $records->pluck('id')->toArray();
                                
                                $newPrimary = $record->product->images()
                                    ->whereNotIn('id', $recordIds)
                                    ->first();
                                
                                if ($newPrimary) {
                                    $newPrimary->update(['is_primary' => true]);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}