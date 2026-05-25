<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventUser extends Model
{
    protected $table = 'event_user';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'payment_status',
        'amount',
        'external_id',
        'invoice_id',
        'payment_url',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
