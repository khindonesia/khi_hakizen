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

                // Generate filename with timestamp for uniqueness
                $filename = 'exports/orders_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

                // Ensure directory exists
                Storage::disk('private')->makeDirectory('exports');
                $filePath = Storage::disk('private')->path($filename);

                // Create CSV writer from path to stream directly to disk (O(1) memory)
                $csv = Writer::createFromPath($filePath, 'w+');

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

                // Efficient query with column selection to reduce payload
                $ordersQuery = Order::query()
                    ->select([
                        'id', 'invoice_id', 'created_at', 'user_id', 'address_id',
                        'subtotal', 'shipping_fee', 'total_amount', 'payment_status', 'status'
                    ])
                    ->with([
                        'user:id,name,email,phone',
                        'address:id,address_line,city,postal_code',
                        'items:id,order_id,product_id,variant_id,quantity',
                        'items.product:id,name',
                        'items.variant:id,sku'
                    ])
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
                    );

                // Process records using lazy cursor chunking to prevent memory bloat (O(1) Memory Complexity)
                $ordersQuery->lazy(500)->each(function ($order) use ($csv) {
                    Log::info('Processing Order', ['invoice_id' => $order->invoice_id]);

                    $itemsList = $order->items->map(function ($item) {
                        $productName = optional($item->product)->name ?? 'N/A';
                        $variantSku = optional($item->variant)->sku ?? '';

                        return $item->quantity . 'x ' . $productName .
                            ($variantSku ? ' (' . $variantSku . ')' : '');
                    })->join(', ');

                    $csv->insertOne([
                        $order->invoice_id,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->user->name,
                        $order->user->email,
                        $order->user->phone ?? '-',
                        $order->address->address_line ?? '-', // FIXED BUG: changed 'address' to 'address_line'
                        $order->address->city ?? '-',
                        $order->address->postal_code ?? '-',
                        $itemsList,
                        $order->subtotal,
                        $order->shipping_fee,
                        $order->total_amount,
                        $order->payment_status,
                        $order->status,
                    ]);
                });

                // Provide download link
                $url = route('files.export', ['filename' => basename($filename)]);

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