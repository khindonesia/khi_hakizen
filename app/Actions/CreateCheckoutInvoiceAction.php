<?php

namespace App\Actions;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Variant;
use App\Services\RajaOngkirShippingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCheckoutInvoiceAction
{
    public function __construct(
        private readonly RajaOngkirShippingService $shippingService,
    ) {
    }

    /**
     * @param array{address_id: int, courier_code: string, service_code: string} $input
     * @return array{order: Order, invoice_items: array<int, array{name: string, quantity: int, price: int|float}>}
     */
    public function handle(User $user, array $input): array
    {
        $address = UserAddress::query()
            ->where('user_id', $user->id)
            ->findOrFail($input['address_id']);

        $cartForWeight = Cart::query()
            ->with('items')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $cartForWeight || $cartForWeight->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $weight = max(1, (int) $cartForWeight->items->sum('quantity')) * 1000;
        $shippingQuote = $this->shippingService->quoteForAddress(
            $address,
            $input['courier_code'],
            $input['service_code'],
            $weight,
        );

        return DB::transaction(function () use ($user, $address, $input, $shippingQuote): array {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $cartItems = $cart->items()->lockForUpdate()->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $subtotal = 0;
            $invoiceItems = [];
            $lockedItems = [];

            foreach ($cartItems as $cartItem) {
                $variant = Variant::query()
                    ->with('product')
                    ->whereKey($cartItem->variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Not enough stock available for {$variant->product->name}.",
                    ]);
                }

                $lineTotal = (int) $variant->price * (int) $cartItem->quantity;
                $subtotal += $lineTotal;

                $lockedItems[] = [
                    'cart_item' => $cartItem,
                    'variant' => $variant,
                    'line_total' => $lineTotal,
                ];

                $invoiceItems[] = [
                    'name' => $variant->product->name,
                    'quantity' => (int) $cartItem->quantity,
                    'price' => (int) $variant->price,
                ];
            }

            $shippingFee = $shippingQuote['cost'];

            $order = Order::query()->create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'courier' => $input['courier_code'],
                'service' => $shippingQuote['service'],
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total_amount' => $subtotal + $shippingFee,
                'payment_status' => 'pending',
                'external_id' => 'order-' . Str::random(10) . '-' . time(),
                'status' => 'pending',
            ]);

            foreach ($lockedItems as $lockedItem) {
                /** @var Variant $variant */
                $variant = $lockedItem['variant'];
                $cartItem = $lockedItem['cart_item'];

                $variant->updateStock($variant->stock_quantity - (int) $cartItem->quantity);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'quantity' => (int) $cartItem->quantity,
                    'price' => (int) $variant->price,
                    'total_price' => $lockedItem['line_total'],
                ]);
            }

            $cart->update(['status' => 'converted']);

            return [
                'order' => $order,
                'invoice_items' => $invoiceItems,
            ];
        });
    }
}
