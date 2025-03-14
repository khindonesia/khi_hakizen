<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    
    protected function afterCreate(): void
    {
        // Create a default variant
        $defaultVariant = $this->record->variants()->create([
            'sku' => 'PROD-' . $this->record->id,
            'variant_name' => 'Default',
            'price' => 0,
            'stock_quantity' => 0,
            'is_default' => true,
            'status' => $this->record->status === 'out_of_stock' ? 'out_of_stock' : 'active'
        ]);
        
        // Show notification
        Notification::make()
            ->title('Product created successfully')
            ->body('Default variant has been created. Please update its price and stock.')
            ->success()
            ->send();
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}