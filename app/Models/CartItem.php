<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity',
        'price'
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the variant associated with the cart item.
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
    
    /**
     * Get the product through variant
     */
    public function product()
    {
        return $this->variant->product();
    }

    public function variantAttributes()
    {
        return $this->variant->variantAttributes; // Ensure variant attributes are loaded
    }

    /**
     * Get the attribute values through variantAttributes.
     */
    public function attributeValues()
    {
        return $this->variant->variantAttributes->map(function($variantAttribute) {
            return $variantAttribute->attributeValue;
        });
    }
}