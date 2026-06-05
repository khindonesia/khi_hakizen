<?php

namespace App\Models;

use Wave\Post as WavePost;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends WavePost
{
    public $guarded = [];

    /**
     * Get the root comments for the post.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    /**
     * Get all comments (including replies) for the post.
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
