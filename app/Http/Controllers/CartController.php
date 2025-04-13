<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Variant;
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

        $variant = Variant::findOrFail($variantId);

        if ($variant->stock_quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $cart = Cart::firstOrCreate(
                ['user_id' => $userId, 'status' => 'active'],
                ['user_id' => $userId]
            );

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('variant_id', $variantId)
                ->first();

            if ($cartItem) {
                if ($variant->stock_quantity < ($cartItem->quantity + $quantity)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not enough stock available for the requested quantity'
                    ], 422);
                }

                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'price' => $variant->price
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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

            $cartItem = CartItem::findOrFail($cartItemId);
            $variant = $cartItem->variant;

            if ($variant->stock_quantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available'
                ], 422);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

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

            $cartItem = CartItem::findOrFail($cartItemId);
            $cartItem->delete();

            $cartTotalPrice = $this->getCartTotalPrice();

            return response()->json([
                'success' => true,
                'message' => 'Cart item removed successfully',
                'data' => [
                    'cartSubtotal' => $cartTotalPrice->getData()->totalPrice
                ]
            ]);
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

            // Get all the cart items for the user's active cart
            $cartItems = CartItem::where('cart_id', $cart->id)->get();

            // Calculate the total price
            $totalPrice = $cartItems->reduce(function ($carry, $item) {
                return $carry + ($item->price * $item->quantity);
            }, 0);

            return response()->json([
                'success' => true,
                'totalPrice' => $totalPrice
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart total price: ' . $e->getMessage()
            ], 500);
        }
    }
}
