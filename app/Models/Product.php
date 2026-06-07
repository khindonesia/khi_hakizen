<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'weight',
        'status',
    ];

    /**
     * Relasi ke tipe produk.
     */
    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, 'typeable');
    }

    /**
     * Mendapatkan harga yang akan ditampilkan.
     */
    public function getDisplayPriceAttribute()
    {
        $variants = $this->relationLoaded('variants')
            ? $this->variants->filter(fn($v) => $v->status === 'active')
            : $this->variants()->active()->get();
        
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
        $defaultVariant = $this->relationLoaded('defaultVariant')
            ? $this->defaultVariant
            : ($this->relationLoaded('variants')
                ? $this->variants->firstWhere('is_default', true)
                : $this->defaultVariant);

        if (!$defaultVariant) {
            $defaultVariant = $variants->first();
        }

        return $defaultVariant ? $defaultVariant->price : 0;
    }

    /**
     * Mendapatkan stok yang tersedia.
     */
    public function getAvailableStockAttribute()
    {
        if ($this->relationLoaded('variants')) {
            $activeVariants = $this->variants->filter(fn($v) => $v->status === 'active');
            if ($activeVariants->count() > 1) {
                return $activeVariants->sum('stock_quantity') ?? 0;
            }
        } else {
            $variantsQuery = $this->variants()->active();
            if ($variantsQuery->count() > 1) {
                return $variantsQuery->sum('stock_quantity') ?? 0;
            }
        }
        
        // Produk dengan satu varian atau varian default
        $defaultVariant = $this->relationLoaded('defaultVariant')
            ? $this->defaultVariant
            : ($this->relationLoaded('variants')
                ? $this->variants->firstWhere('is_default', true)
                : $this->defaultVariant);

        if (!$defaultVariant) {
            $defaultVariant = $this->relationLoaded('variants')
                ? $this->variants->first()
                : $this->variants()->first();
        }

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