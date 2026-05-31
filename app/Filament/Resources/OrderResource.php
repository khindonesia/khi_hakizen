<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Order Management';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'invoice_id';

    

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 0
            ? 'warning'
            : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Order Information Section
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_id')
                            ->label('Invoice ID')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('address_id')
                            ->relationship('address', 'address_line')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipping' => 'Shipping',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                // Shipping Details Section
                Forms\Components\Section::make('Shipping Details')
                    ->schema([
                        Forms\Components\TextInput::make('courier')
                            ->required(),
                        Forms\Components\TextInput::make('service')
                            ->required(),
                        Forms\Components\TextInput::make('shipping_fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('resi')
                            ->label('Nomor Resi')
                            ->placeholder('Masukkan nomor resi pengiriman'),
                    ])
                    ->columns(2),

                // Payment Details Section
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('external_id')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('xendit_invoice_id')
                            ->label('Xendit Invoice ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('payment_url')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_id')
                    ->label('Invoice ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('resi')
                    ->label('No. Resi')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make('total_amount')
                            ->money('IDR'),
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipping' => 'primary',
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
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipping' => 'Shipping',
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
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->columns(2)
                    ->columnSpan(2),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('printInvoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (Order $record): string => route('orders.print-invoice', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('printDeliveryOrder')
                    ->label('Print Delivery Order')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->url(fn (Order $record): string => route('orders.print-delivery-order', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('inputResi')
                    ->label('Input Resi')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('resi')
                            ->label('Nomor Resi')
                            ->default(fn (Order $record): ?string => $record->resi)
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'resi' => $data['resi'],
                            'status' => 'shipping',
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Nomor resi berhasil disimpan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('trackShipment')
                    ->label('Lacak')
                    ->icon('heroicon-o-map-pin')
                    ->color('warning')
                    // ->visible(fn (Order $record): bool => !empty($record->resi))
                    ->visible(false)
                    ->modalHeading(fn (Order $record): string => "Lacak Resi: {$record->resi} ({$record->courier})")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Order $record) {
                        return view('filament.components.tracking-info', [
                            'getRecord' => $record,
                        ]);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    FilamentExportBulkAction::make('export'),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'shipping' => 'Shipping',
                                    'delivered' => 'Delivered',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'address'])
            ->withCount('items');
    }
}
