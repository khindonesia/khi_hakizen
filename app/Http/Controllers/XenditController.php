<?php

namespace App\Http\Controllers;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class XenditController extends Controller
{
    private $xenditInvoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        $this->xenditInvoiceApi = new InvoiceApi();
    }

    public function createInvoice(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'courier' => 'required|string',
            'service' => 'required|string',
            'shipping_fee' => 'required|numeric',
        ]);

        // Get user's active cart
        $user = auth()->user();
        $cart = Cart::with(['items.variant.product', 'items.variant.variantAttributes.attributeValue'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is empty'
            ], 400);
        }

        // Calculate total amount
        $subtotal = $cart->getTotalPrice();
        $shippingFee = $request->shipping_fee;
        $grandTotal = $subtotal + $shippingFee;

        // Generate unique external ID
        $externalId = 'order-' . Str::random(10) . '-' . time();

        // Create order in database
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $request->address_id,
            'courier' => $request->courier,
            'service' => $request->service,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total_amount' => $grandTotal,
            'payment_status' => 'pending',
            'external_id' => $externalId,
            'status' => 'pending'
        ]);

        // Create order items
        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->variant->product->id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total_price' => $item->price * $item->quantity
            ]);
        }

        // Prepare item descriptions for invoice
        $items = $cart->items->map(function ($item) {
            return [
                'name' => $item->variant->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        })->toArray();

        // Create Xendit invoice
        try {
            $invoiceRequest = new CreateInvoiceRequest([
                'external_id' => $externalId,
                'amount' => $grandTotal,
                'description' => "Order #" . $order->id,
                'invoice_duration' => 86400, // 24 hours
                'currency' => 'IDR',
                'success_redirect_url' => route('payment.success', ['order_id' => $order->id]),
                'failure_redirect_url' => route('payment.failed', ['order_id' => $order->id]),
                'customer' => [
                    'given_names' => $user->name,
                    'email' => $user->email,
                ],
                'items' => $items,
            ]);

            $response = $this->xenditInvoiceApi->createInvoice($invoiceRequest);

            // Update order with invoice ID and payment URL
            $order->update([
                'invoice_id' => $response['id'],
                'payment_url' => $response['invoice_url']
            ]);

            // Mark cart as inactive
            $cart->update(['status' => 'converted']);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'invoice_url' => $response['invoice_url']
                ]
            ]);
        } catch (\Exception $e) {
            // If there's an error, delete the order
            $order->delete();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {

        $externalId = $request->external_id;
        $status = $request->status;

        // Find order by external ID
        $order = Order::where('external_id', $externalId)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Update order status based on Xendit callback
        if ($status === 'PAID') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);
        } elseif ($status === 'EXPIRED') {
            $order->update([
                'payment_status' => 'expired',
                'status' => 'cancelled'
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
