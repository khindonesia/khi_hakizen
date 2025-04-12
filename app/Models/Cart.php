<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status'
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get active cart for a user
     */
    public static function getActiveCart($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['user_id' => $userId]
        );
    }
    
    /**
     * Calculate total price of all items in cart
     */
    public function getTotalPrice()
    {
        return $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }
}