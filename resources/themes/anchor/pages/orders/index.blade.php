<?php
    use App\Models\Order;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Filament\Forms\Contracts\HasForms;
    use Filament\Tables;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Filament\Tables\Filters\SelectFilter;
    use Filament\Tables\Enums\FiltersLayout;
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};
    
    middleware('auth');
    name('orders');
    
    new class extends Component implements HasForms, Tables\Contracts\HasTable
    {
        use InteractsWithForms, Tables\Concerns\InteractsWithTable;
        
        public ?array $data = [];
        
        public function table(Table $table): Table
        {
            return $table
                ->query(Order::query()->where('user_id', auth()->id()))
                ->columns([
                    TextColumn::make('invoice_id')
                        ->label('Invoice ID')
                        ->searchable()
                        ->sortable()
                        ->copyable(),
                    TextColumn::make('total_amount')
                        ->money('IDR')
                        ->sortable(),
                    TextColumn::make('status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                        })
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('payment_status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'info',
                        })
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->label('Order Date')
                        ->dateTime()
                        ->sortable(),
                ])
                ->filters([
                    SelectFilter::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'processing' => 'Processing',
                            'shipped' => 'Shipped',
                            'delivered' => 'Delivered',
                            'cancelled' => 'Cancelled',
                        ]),
                    SelectFilter::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ]),
                    Tables\Filters\Filter::make('created_at')
                        ->form([
                            \Filament\Forms\Components\DatePicker::make('created_from')
                                ->label('Created from'),
                            \Filament\Forms\Components\DatePicker::make('created_until')
                                ->label('Created until'),
                        ])
                        ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                            return $query
                                ->when(
                                    $data['created_from'],
                                    fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['created_until'],
                                    fn(\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        }),
                ])
                ->filtersLayout(FiltersLayout::AboveContent)
                ->filtersFormColumns(3) // This is the key change - sets filters to display in 3 columns
                ->actions([
                    Tables\Actions\ViewAction::make()
                        ->url(fn(Order $record): string => "/orders/{$record->id}"),
                ])
                ->defaultSort('created_at', 'desc');
        }
    }
?>

<x-layouts.app>
    @volt('orders')
        <x-app.container>
            <div class="flex items-center justify-between mb-5">
                <x-app.heading title="My Orders" description="Track and manage your orders" :border="false" />
            </div>
            <div class="overflow-x-auto border rounded-lg">
                {{ $this->table }}
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>