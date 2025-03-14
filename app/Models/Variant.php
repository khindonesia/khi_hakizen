<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock_quantity',
        'image_url',
        'is_default',
        'status',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant attributes for the variant.
     */
    public function variantAttributes(): HasMany
    {
        return $this->hasMany(VariantAttribute::class);
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0 && $this->status !== 'out_of_stock';
    }

    /**
     * Update stock quantity
     */
    public function updateStock(int $quantity): self
    {
        $this->stock_quantity = $quantity;
        
        // Automatic status update based on stock
        if ($quantity <= 0 && $this->status !== 'out_of_stock') {
            $this->status = 'out_of_stock';
        } elseif ($quantity > 0 && $this->status === 'out_of_stock') {
            $this->status = 'active';
        }
        
        $this->save();
        return $this;
    }

    /**
     * Reduce stock by specified amount
     */
    public function reduceStock(int $amount = 1): self
    {
        return $this->updateStock(max(0, $this->stock_quantity - $amount));
    }

    /**
     * Increase stock by specified amount
     */
    public function increaseStock(int $amount = 1): self
    {
        return $this->updateStock($this->stock_quantity + $amount);
    }

    /**
     * Scope a query to only include active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include in-stock variants.
     */
    public function scopeInStock($query)
    {
        return $query->where('status', '!=', 'out_of_stock')
                     ->where('stock_quantity', '>', 0);
    }
    
    /**
     * Scope a query to only include default variants.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    
    /**
     * Observer untuk otomatis mengupdate status produk ketika default variant berubah
     */
    protected static function booted()
    {
        static::saved(function ($variant) {
            // Update stock status jika diperlukan
            if ($variant->stock_quantity <= 0 && $variant->status !== 'out_of_stock') {
                $variant->status = 'out_of_stock';
                $variant->saveQuietly();
            } elseif ($variant->stock_quantity > 0 && $variant->status === 'out_of_stock') {
                $variant->status = 'active';
                $variant->saveQuietly();
            }
        });
    }
}