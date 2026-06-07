<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Type extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * Get all products that are assigned this type.
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'typeable');
    }

    /**
     * Get all posts that are assigned this type.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'typeable');
    }

    /**
     * Get all events that are assigned this type.
     */
    public function events(): MorphToMany
    {
        return $this->morphedByMany(Event::class, 'typeable');
    }
}
