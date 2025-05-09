<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('printInvoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                // ->url(fn ($record) => route('orders.print-invoice', $record))
                ->openUrlInNewTab(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Details')
                    ->schema([
                        TextEntry::make('invoice_id')
                            ->label('Invoice ID')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            }),
                        TextEntry::make('payment_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                                'refunded' => 'info',
                            }),
                    ])
                    ->columns(4),
                    
                Grid::make(2)
                    ->schema([
                        Section::make('Customer Information')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Customer Name'),
                                TextEntry::make('user.email')
                                    ->label('Email'),
                                TextEntry::make('user.phone')
                                    ->label('Phone'),
                            ]),
                            
                        Section::make('Shipping Information')
                            ->schema([
                                TextEntry::make('address.address')
                                    ->label('Address'),
                                TextEntry::make('address.city')
                                    ->label('City'),
                                TextEntry::make('address.postal_code')
                                    ->label('Postal Code'),
                                TextEntry::make('courier')
                                    ->label('Courier'),
                                TextEntry::make('service')
                                    ->label('Service'),
                            ]),
                    ]),
                    
                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->money('IDR'),
                        TextEntry::make('shipping_fee')
                            ->money('IDR'),
                        TextEntry::make('total_amount')
                            ->money('IDR')
                            ->weight('bold'),
                        TextEntry::make('external_id')
                            ->label('Payment Reference')
                            ->copyable()
                            ->visible(fn ($record) => !empty($record->external_id)),
                        TextEntry::make('payment_url')
                            ->label('Payment URL')
                            ->url(fn ($record) => $record->payment_url)
                            ->openUrlInNewTab()
                            ->visible(fn ($record) => !empty($record->payment_url)),
                    ])
                    ->columns(3),
                    
                Section::make('Order Timeline')
                    ->schema([
                        // This would require a separate order_histories table to track status changes
                        // Placeholder for demonstration purposes
                        IconEntry::make('created_at')
                            ->label('Order Created')
                            ->icon('heroicon-o-shopping-cart')
                            ->color('success'),
                    ]),
            ]);
    }
}