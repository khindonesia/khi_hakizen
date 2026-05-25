<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    public function printInvoice(int|string $order)
    {
        $order = Order::query()
            ->with(['user', 'items.product', 'address'])
            ->where('user_id', auth()->id())
            ->findOrFail($order);
        
        // Generate PDF
        $pdf = PDF::loadView('invoices.print', [
            'order' => $order
        ]);
        
        // Return PDF for download or viewing
        return $pdf->stream("invoice-{$order->invoice_id}.pdf");
        
        // Alternative: force download
        // return $pdf->download("invoice-{$order->invoice_id}.pdf");
    }
}
