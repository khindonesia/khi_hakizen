<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    public function printInvoice(int|string $order)
    {
        $query = Order::query()
            ->with(['user', 'items.product', 'address']);

        if (!auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        $order = $query->findOrFail($order);
        
        return view('invoices.print', [
            'order' => $order
        ]);
    }
}
