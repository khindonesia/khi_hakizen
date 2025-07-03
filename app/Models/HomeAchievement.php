<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomeAchievement extends Model
{
    use HasFactory;

    protected $table = 'home_achievements';

    protected $fillable = [
        'home_page_content_id',
        'achievement_title',
        'display_order',
    ];

    public function homePageContent()
    {
        return $this->belongsTo(HomePageContent::class, 'home_page_content_id');
    }


    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
}
