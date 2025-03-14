<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'address_line',
        'city',
        'state',
        'postal_code',
        'country',
        'is_primary',
        'phone_number',
        'address_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user that owns the address.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set this address as the primary address for the user
     * and update all other addresses to non-primary.
     *
     * @return bool
     */
    public function setPrimary()
    {
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Set semua alamat user ini menjadi non-primary
            self::where('user_id', $this->user_id)
                ->update(['is_primary' => false]);
                
            // Set alamat ini menjadi primary
            $this->is_primary = true;
            $this->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Observer untuk mengelola is_primary pada saat save/update
        static::saving(function ($address) {
            // Jika alamat ini di-set sebagai primary
            if ($address->is_primary) {
                // Set semua alamat milik user yang sama menjadi non-primary
                self::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id ?? 0)
                    ->update(['is_primary' => false]);
            }
        });

        // Untuk memastikan user selalu memiliki alamat primary jika ini adalah alamat pertamanya
        static::created(function ($address) {
            // Jika ini alamat pertama user, jadikan sebagai primary
            $count = self::where('user_id', $address->user_id)->count();
            if ($count === 1) {
                $address->is_primary = true;
                $address->save();
            }
        });
    }

    /**
     * Scope untuk query alamat utama
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}