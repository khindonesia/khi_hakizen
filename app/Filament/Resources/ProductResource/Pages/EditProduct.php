<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
    
    protected ?string $maxContentWidth = '7xl';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
    
    public function getTitle(): string
    {
        return "Edit Product: {$this->record->name}";
    }
    
    public function getLayoutData(): array
    {
        return [
            'hasSidebar' => true
        ];
    }
    
    public function getRelationManagers(): array
    {
        return [];
    }
    
    public function getContentTabLabel(): string 
    {
        return 'Product Details';
    }
    
    protected function getHeaderWidgets(): array 
    {
        return [];
    }
    
    protected function getFooterWidgets(): array 
    {
        return [];
    }
    
    protected function getSidebarRelationManagers(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
        ];
    }
}