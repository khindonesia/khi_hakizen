<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'title',
        'content',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
