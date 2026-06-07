<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $facebook = DB::table('settings')->where('key', 'socmed_facebook')->value('value') ?? 'https://facebook.com/komunitashistoria';
        $instagram = DB::table('settings')->where('key', 'socmed_instagram')->value('value') ?? 'https://instagram.com/komunitashistoria';
        $twitter = DB::table('settings')->where('key', 'socmed_twitter')->value('value') ?? 'https://twitter.com/historiadotco';
        $youtube = DB::table('settings')->where('key', 'socmed_youtube')->value('value') ?? 'https://youtube.com';

        $socialLinks = [
            [
                'name' => 'Facebook',
                'url' => $facebook,
                'logo' => '',
            ],
            [
                'name' => 'Instagram',
                'url' => $instagram,
                'logo' => '',
            ],
            [
                'name' => 'Twitter',
                'url' => $twitter,
                'logo' => '',
            ],
            [
                'name' => 'YouTube',
                'url' => $youtube,
                'logo' => '',
            ],
        ];

        DB::table('settings')->updateOrInsert(
            ['key' => 'site_social_links'],
            [
                'display_name' => 'Social Media Links',
                'value' => json_encode($socialLinks),
                'type' => 'json',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Delete deprecated keys
        DB::table('settings')->whereIn('key', [
            'socmed_facebook',
            'socmed_instagram',
            'socmed_twitter',
            'socmed_youtube',
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $socialLinksJson = DB::table('settings')->where('key', 'site_social_links')->value('value');
        $socialLinks = json_decode($socialLinksJson, true) ?: [];

        $facebook = '';
        $instagram = '';
        $twitter = '';
        $youtube = '';

        foreach ($socialLinks as $link) {
            $name = strtolower($link['name'] ?? '');
            if ($name === 'facebook') {
                $facebook = $link['url'] ?? '';
            } elseif ($name === 'instagram') {
                $instagram = $link['url'] ?? '';
            } elseif ($name === 'twitter') {
                $twitter = $link['url'] ?? '';
            } elseif ($name === 'youtube') {
                $youtube = $link['url'] ?? '';
            }
        }

        $deprecatedSettings = [
            [
                'key' => 'socmed_facebook',
                'value' => $facebook ?: 'https://facebook.com/komunitashistoria',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_instagram',
                'value' => $instagram ?: 'https://instagram.com/komunitashistoria',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_twitter',
                'value' => $twitter ?: 'https://twitter.com/historiadotco',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_youtube',
                'value' => $youtube ?: 'https://youtube.com',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($deprecatedSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'group' => $setting['group'],
                    'created_at' => $setting['created_at'],
                    'updated_at' => $setting['updated_at'],
                ]
            );
        }

        DB::table('settings')->where('key', 'site_social_links')->delete();
    }
};
