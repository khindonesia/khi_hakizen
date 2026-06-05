<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'title',
        'seo_title',
        'body',
        'image',
        'location',
        'slug',
        'meta_description',
        'meta_keywords',
        'status',
        'type',
        'price',
        'capacity',
        'start_datetime',
        'end_datetime'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    /**
     * Get the author that owns the event.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the users registered for the event.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot(['status', 'payment_status', 'amount', 'external_id', 'invoice_id', 'payment_url'])
            ->withTimestamps();
    }

    /**
     * Get the root comments for the event.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    /**
     * Get all comments (including replies) for the event.
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Scope a query to only include published events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'PUBLISHED');
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>=', now());
    }

    /**
     * Scope a query to only include past events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePast($query)
    {
        return $query->where('end_datetime', '<', now());
    }

    /**
     * Scope a query to only include ongoing events.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_datetime', '<=', now())
                     ->where('end_datetime', '>=', now());
    }

    /**
     * Check if the event is full.
     */
    public function isFull(): bool
    {
        if ($this->capacity === null) {
            return false;
        }

        $activeRegistrationsCount = \Illuminate\Support\Facades\DB::table('event_user')
            ->where('event_id', $this->id)
            ->whereIn('status', ['active', 'pending'])
            ->count();

        return $activeRegistrationsCount >= $this->capacity;
    }
}