<?php

namespace App\Models;

use Wave\Post as WavePost;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Aspirasi extends WavePost
{
    public $guarded = [];

    /**
     * Get the types for the aspiration.
     */
    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, 'typeable');
    }

    /**
     * Get the root comments for the aspiration.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    /**
     * Get all comments (including replies) for the aspiration.
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
