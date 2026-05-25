<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Variant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to add items to cart'
            ], 401);
        }

        $userId = Auth::id();
        $variantId = $request->variant_id;
        $quantity = $request->quantity;

        try {
            DB::transaction(function () use ($userId, $variantId, $quantity): void {
                $variant = Variant::query()
                    ->whereKey($variantId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock_quantity < $quantity) {
                    throw new \RuntimeException('Not enough stock available', 422);
                }

                $cart = Cart::firstOrCreate(
                    ['user_id' => $userId, 'status' => 'active'],
                    ['user_id' => $userId]
                );

                $cartItem = CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if ($cartItem) {
                    if ($variant->stock_quantity < ($cartItem->quantity + $quantity)) {
                        throw new \RuntimeException('Not enough stock available for the requested quantity', 422);
                    }

                    $cartItem->quantity += $quantity;
                    $cartItem->save();

                    return;
                }

                CartItem::create([
                    'cart_id' => $cart->id,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $variant->price
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully'
            ]);
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 422) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            throw $exception;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCartItem(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to update cart items'
                ], 401);
            }

            $cartItem = DB::transaction(function () use ($cartItemId, $request): CartItem {
                $cartItem = $this->activeUserCartItem($cartItemId)->lockForUpdate()->firstOrFail();
                $variant = Variant::query()
                    ->whereKey($cartItem->variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock_quantity < $request->quantity) {
                    throw new \RuntimeException('Not enough stock available', 422);
                }

                $cartItem->quantity = $request->quantity;
                $cartItem->save();

                return $cartItem;
            });

            // Call getCartTotalPrice() method
            $cartTotalPrice = $this->getCartTotalPrice();

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'updatedItemPrice' => $cartItem->price * $cartItem->quantity,
                    'cartSubtotal' => $cartTotalPrice->getData()->totalPrice // Accessing the totalPrice from the response
                ]
            ]);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 422) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            throw $exception;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart item: ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleteCartItem($cartItemId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to remove items from cart'
                ], 401);
            }

            $cartItem = $this->activeUserCartItem($cartItemId)->firstOrFail();
            $cartItem->delete();

            $cartTotalPrice = $this->getCartTotalPrice();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully',
                'data' => [
                    'cartSubtotal' => $cartTotalPrice->getData()->totalPrice
                ]
            ]);
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove cart item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCartTotalPrice()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to view cart total'
                ], 401);
            }

            $userId = Auth::id();

            // Find the active cart for the user
            $cart = Cart::where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active cart found'
                ], 404);
            }

            $totalPrice = CartItem::query()
                ->where('cart_id', $cart->id)
                ->selectRaw('COALESCE(SUM(price * quantity), 0) as total')
                ->value('total');

            return response()->json([
                'success' => true,
                'totalPrice' => (int) $totalPrice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart total price: ' . $e->getMessage()
            ], 500);
        }
    }

    private function activeUserCartItem(int|string $cartItemId)
    {
        return CartItem::query()
            ->whereKey($cartItemId)
            ->whereHas('cart', function ($query): void {
                $query->where('user_id', Auth::id())
                    ->where('status', 'active');
            });
    }
}
