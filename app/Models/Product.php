<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'status',
    ];

    /**
     * Mendapatkan harga yang akan ditampilkan.
     */
    public function getDisplayPriceAttribute()
    {
        $variants = $this->variants()->active()->get();
        
        if ($variants->count() > 1) {
            $minPrice = $variants->min('price') ?? 0;
            $maxPrice = $variants->max('price') ?? 0;
            
            if ($minPrice === $maxPrice) {
                return $minPrice;
            }
            
            return [
                'min' => $minPrice,
                'max' => $maxPrice
            ];
        }
        
        // Produk dengan satu varian atau varian default
        $defaultVariant = $this->defaultVariant;
        return $defaultVariant ? $defaultVariant->price : 0;
    }

    /**
     * Mendapatkan stok yang tersedia.
     */
    public function getAvailableStockAttribute()
    {
        $variants = $this->variants()->active();
        
        if ($variants->count() > 1) {
            return $variants->sum('stock_quantity') ?? 0;
        }
        
        // Produk dengan satu varian atau varian default
        $defaultVariant = $this->defaultVariant;
        return $defaultVariant ? $defaultVariant->stock_quantity : 0;
    }

    /**
     * Relasi ke kategori produk.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Relasi ke semua varian produk.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * Relasi ke varian default produk (untuk produk simple).
     */
    public function defaultVariant()
    {
        return $this->hasOne(Variant::class)->where('is_default', true);
    }

    /**
     * Relasi ke atribut produk.
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Relasi ke gambar produk.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relasi ke gambar utama produk.
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Membuat varian default jika produk tidak memiliki varian.
     */
    public function createDefaultVariant($price, $stock, $sku = null)
    {
        if (!$this->defaultVariant) {
            $sku = $sku ?? 'PROD-' . $this->id;
            
            return $this->variants()->create([
                'sku' => $sku,
                'price' => $price,
                'stock_quantity' => $stock,
                'is_default' => true,
                'status' => 'active'
            ]);
        }
        
        return $this->defaultVariant;
    }
}