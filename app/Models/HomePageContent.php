<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomePageContent extends Model
{
    use HasFactory;

    protected $table = 'home_page_contents';

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_description',
        'hero_image',
        'hero_button_text',
        'org_name',
        'org_acronym',
        'org_description',
        'leader_name',
        'leader_position',
        'leader_image',
        'leader_bio',
    ];

    public function achievements()
    {
        return $this->hasMany(HomeAchievement::class, 'home_page_content_id');
    }
}
