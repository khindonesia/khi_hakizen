<?php

namespace App\Models;

use Illuminate\Support\Str;
use Wave\User as WaveUser;
use Illuminate\Notifications\Notifiable;
use Wave\Traits\HasProfileKeyValues;

class User extends WaveUser
{
    use Notifiable, HasProfileKeyValues;

    public $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'avatar',
        'password',
        'role_id',
        'verification_code',
        'verified',
        'trial_ends_at',
        'phone_number', 
        'age',          
        'occupation',    
        'reason_for_joining', 
        'consent',
        'birth_year',
        'country',
        'province',
        'city',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the events this user is registered for.
     */
    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'event_user')
            ->withPivot(['status', 'payment_status', 'amount', 'external_id', 'invoice_id', 'payment_url'])
            ->withTimestamps();
    }

    /**
     * Alias relationship for Filament many-to-many attach/detach actions.
     */
    public function events()
    {
        return $this->registeredEvents();
    }

    /**
     * Get the events created/owned by this user.
     */
    public function ownedEvents()
    {
        return $this->hasMany(Event::class, 'author_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        // Listen for the creating event of the model
        static::creating(function ($user) {
            // Check if the username attribute is empty
            if (empty($user->username)) {
                // Use the name to generate a slugified username
                $username = Str::slug($user->name, '');
                $i = 1;
                while (self::where('username', $username)->exists()) {
                    $username = Str::slug($user->name, '') . $i;
                    $i++;
                }
                $user->username = $username;
            }
        });

        // Listen for the created event of the model
        static::created(function ($user) {
            // Remove all roles
            $user->syncRoles([]);
            // Assign the default role
            $user->assignRole( config('wave.default_user_role', 'registered') );
        });
    }
}
