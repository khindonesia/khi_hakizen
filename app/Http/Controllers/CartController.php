<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Add item to cart
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function addToCart(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
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
            // Get price from variant model
            $price = $variant->price;

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
                        'price' => $price
                    ]);
                }

                DB::commit();

                $cartCount = CartItem::where('cart_id', $cart->id)->sum('quantity');

                return response()->json([
                    'success' => true,
                    'message' => 'Item added to cart successfully',
                    'cart_count' => $cartCount
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to cart: ' . $e->getMessage()
            ], 500);
        }
    }
}
