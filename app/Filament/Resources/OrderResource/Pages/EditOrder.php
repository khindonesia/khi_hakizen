<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('sendInvoice')
                ->label('Send Invoice')
                ->icon('heroicon-o-envelope')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    // Implement your send invoice logic here
                    
                    Notification::make()
                        ->title('Invoice sent successfully')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('printInvoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('orders.print-invoice', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('printDeliveryOrder')
                ->label('Print Delivery Order')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->url(fn ($record) => route('orders.print-delivery-order', $record))
                ->openUrlInNewTab(),
        ];
    }
}