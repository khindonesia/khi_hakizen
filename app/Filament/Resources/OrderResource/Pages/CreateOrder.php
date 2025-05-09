<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a unique invoice ID
        $data['invoice_id'] = 'INV-' . Str::upper(Str::random(8));
        
        // If external_id is empty, generate one
        if (empty($data['external_id'])) {
            $data['external_id'] = Str::uuid()->toString();
        }
        
        // Calculate the subtotal if not provided
        if (!isset($data['subtotal'])) {
            $data['subtotal'] = $data['total_amount'] - $data['shipping_fee'];
        }
        
        return $data;
    }
}