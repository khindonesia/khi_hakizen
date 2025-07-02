<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    public function printInvoice(Order $order)
    {
        // Load any additional relationships needed for the invoice
        $order->load(['user', 'items', 'address']);
        
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