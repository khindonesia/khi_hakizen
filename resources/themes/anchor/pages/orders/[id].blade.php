<?php
    use App\Models\Order;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Filament\Forms\Contracts\HasForms;
    use Filament\Infolists;
    use Filament\Infolists\Infolist;
    use Filament\Infolists\Components\TextEntry;
    use Filament\Infolists\Components\Section;
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};
    
    middleware('auth');
    name('orders.view');
    
    new class extends Component implements HasForms, Infolists\Contracts\HasInfolists
    {
        use InteractsWithForms, Infolists\Concerns\InteractsWithInfolists;
        
        public Order $order;
        
	        public function mount($id)
	        {
	            $this->order = Order::query()
                    ->with(['address', 'items.product'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($id);
	        }
        
        public function infolist(Infolist $infolist): Infolist
        {
            return $infolist
                ->record($this->order)
                ->schema([
                    Section::make('Order Information')
                        ->schema([
                            TextEntry::make('invoice_id')
                                ->label('Invoice ID'),
                            TextEntry::make('created_at')
                                ->label('Order Date')
                                ->dateTime(),
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                }),
                            TextEntry::make('payment_status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'failed' => 'danger',
                                    'refunded' => 'info',
                                }),
                        ])
                        ->columns(2),
                    
                    Section::make('Shipping Details')
                        ->schema([
                            TextEntry::make('courier'),
                            TextEntry::make('service'),
                            TextEntry::make('shipping_fee')
                                ->money('IDR'),
                            TextEntry::make('address.address_line')
                                ->label('Shipping Address'),
                        ])
                        ->columns(2),
                    
                    Section::make('Payment Details')
                        ->schema([
                            TextEntry::make('subtotal')
                                ->money('IDR'),
                            TextEntry::make('shipping_fee')
                                ->money('IDR'),
                            TextEntry::make('total_amount')
                                ->money('IDR'),
                            // Perbaikan pada bagian payment_url:
                            TextEntry::make('payment_url')
                                ->label('Payment Link')
                                ->formatStateUsing(fn ($state) => $state ? 'Pay Now' : '')
                                ->url(fn ($record) => $record->payment_url)
                                ->openUrlInNewTab()
                                ->visible(fn($record) => $record->payment_status === 'pending' && $record->payment_url),
                        ])
                        ->columns(2),
                    
                    Section::make('Order Items')
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('items')
                                ->schema([
                                    TextEntry::make('product.name')
                                        ->label('Product'),
                                    TextEntry::make('price')
                                        ->money('IDR'),
                                    TextEntry::make('quantity'),
                                    TextEntry::make('subtotal')
                                        ->state(fn($record) => $record->price * $record->quantity)
                                        ->money('IDR'),
                                ])
                                ->columns(4),
                        ]),
                ]);
        }
    }
?>

<x-layouts.app>
    @volt('orders.view')
        <x-app.container>
            <div class="mb-5">
                <div class="flex items-center justify-between">
                    <x-app.heading title="Order Details" description="View your order information" :border="false" />
                    <x-button tag="a" href="/orders">Back to Orders</x-button>
                </div>
                
                <div class="mt-5">
                    {{ $this->infolist }}
                </div>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>
