<?php

namespace App\Filament\Actions;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Illuminate\Support\Facades\Log;


class ExportOrdersAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('export_orders')
            ->label('Export Orders')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (array $data): void {
                // Log the date_range data
                Log::info('Export Orders Action Started', [
                    'from_date' => $data['from_date'] ?? 'No start date',
                    'to_date' => $data['to_date'] ?? 'No end date'
                ]);

                // Efficient query with eager loading to minimize database calls
                $orders = Order::query()
                    ->with(['user', 'address', 'items.product', 'items.variant'])
                    ->when(
                        $data['from_date'] ?? null,
                        fn($query, $fromDate) => $query->whereDate('created_at', '>=', $fromDate)
                    )
                    ->when(
                        $data['to_date'] ?? null,
                        fn($query, $toDate) => $query->whereDate('created_at', '<=', $toDate)
                    )
                    ->when(
                        $data['status'] ?? null,
                        fn($query, $status) => $query->where('status', $status)
                    )
                    ->when(
                        $data['payment_status'] ?? null,
                        fn($query, $paymentStatus) => $query->where('payment_status', $paymentStatus)
                    )
                    ->get();

                // Log the query result count
                Log::info('Orders fetched', ['count' => $orders->count()]);

                // Create CSV with optimized memory usage
                $csv = Writer::createFromString();

                // Add headers
                $csv->insertOne([
                    'Invoice ID',
                    'Order Date',
                    'Customer',
                    'Email',
                    'Phone',
                    'Address',
                    'City',
                    'Postal Code',
                    'Items',
                    'Subtotal',
                    'Shipping Fee',
                    'Total Amount',
                    'Payment Status',
                    'Order Status',
                ]);

                // Add data
                foreach ($orders as $order) {
                    Log::info('Processing Order', ['invoice_id' => $order->invoice_id]);

                    $itemsList = $order->items->map(function ($item) {
                        // Log each item to check for unexpected arrays
                        Log::info('Processing Item', [
                            'product_name' => optional($item->product)->name,
                            'variant_name' => optional($item->variant)->name,
                            'quantity' => $item->quantity
                        ]);

                        // Use optional() to handle potential null values for product or variant
                        $productName = optional($item->product)->name ?? 'N/A';  // Default if null
                        $variantName = optional($item->variant)->name ?? '';     // Default if null

                        return $item->quantity . 'x ' . $productName .
                            ($variantName ? ' (' . $variantName . ')' : '');
                    })->join(', ');

                    // Log the itemsList being inserted
                    Log::info('Items List', ['items' => $itemsList]);

                    $csv->insertOne([
                        $order->invoice_id,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->user->name,
                        $order->user->email,
                        $order->user->phone ?? '-',
                        $order->address->address,
                        $order->address->city,
                        $order->address->postal_code,
                        $itemsList,
                        $order->subtotal,
                        $order->shipping_fee,
                        $order->total_amount,
                        $order->payment_status,
                        $order->status,
                    ]);
                }

                // Generate filename with timestamp for uniqueness
                $filename = 'orders_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

                // Save to storage
                Storage::disk('public')->put('exports/' . $filename, $csv->toString());

                // Provide download link
                $url = Storage::url('exports/' . $filename);

                Notification::make()
                    ->title('Export completed')
                    ->success()
                    ->body('Your order export is ready for download.')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('download')
                            ->label('Download CSV')
                            ->url($url)
                            ->openUrlInNewTab(),
                    ])
                    ->send();
            })
            ->form([
                \Filament\Forms\Components\DatePicker::make('from_date')
                    ->label('From Date')
                    ->default(now()->subMonth()),
                \Filament\Forms\Components\DatePicker::make('to_date')
                    ->label('To Date')
                    ->default(now()),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipping' => 'Shipping',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->placeholder('All Statuses'),
                \Filament\Forms\Components\Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->placeholder('All Payment Statuses'),
            ]);
    }
}