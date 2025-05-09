<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Widgets\OrderStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Actions\ExportOrdersAction;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // Di file ListOrders.php
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportOrdersAction::make('export_orders')
                ->label('Export Orders')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->count();
                }),
            'pending' => Tab::make('Pending')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->where('status', 'pending')->count();
                })
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending')),
            'processing' => Tab::make('Processing')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->where('status', 'processing')->count();
                })
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'processing')),
            'shipped' => Tab::make('Shipped')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->where('status', 'shipped')->count();
                })
                ->badgeColor('primary')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'shipped')),
            'delivered' => Tab::make('Delivered')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->where('status', 'delivered')->count();
                })
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'delivered')),
            'cancelled' => Tab::make('Cancelled')
                ->badge(function () {
                    return $this->getResource()::getEloquentQuery()->where('status', 'cancelled')->count();
                })
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
