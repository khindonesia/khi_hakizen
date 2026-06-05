<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->truncate();

        $settings = [
            // General
            [
                'key' => 'site.title',
                'value' => 'Komunitas Historia Indonesia',
                'type' => 'text',
                'group' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.description',
                'value' => 'Komunitas Historia Indonesia, penjaga memori kolektif bangsa melalui sejarah, kearsipan, kebudayaan, dan pariwisata.',
                'type' => 'text',
                'group' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site.google_analytics_tracking_id',
                'value' => '',
                'type' => 'text',
                'group' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_logo',
                'value' => 'images/logo.jpg',
                'type' => 'image',
                'group' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_favicon',
                'value' => '',
                'type' => 'image',
                'group' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Header
            [
                'key' => 'header_tagline',
                'value' => 'Scholarly preservation for modern minds. Clean navigation, focused content, and a lighter canvas for the community.',
                'type' => 'text',
                'group' => 'Header',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Footer
            [
                'key' => 'footer_address',
                'value' => 'Jl. Kemudi No. 17, RT.1/RW.1, Kuningan, Jakarta Selatan, Jakarta, 12940',
                'type' => 'textarea',
                'group' => 'Footer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'footer_copyright',
                'value' => '&copy; 2003-' . date('Y') . ' Komunitas Historia Indonesia.',
                'type' => 'text',
                'group' => 'Footer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'footer_contact_phone',
                'value' => '+62 818-0807-3636',
                'type' => 'text',
                'group' => 'Footer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'footer_contact_email',
                'value' => 'info@komunitashistoria.com',
                'type' => 'text',
                'group' => 'Footer',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Social Media
            [
                'key' => 'socmed_facebook',
                'value' => 'https://facebook.com/komunitashistoria',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_instagram',
                'value' => 'https://instagram.com/komunitashistoria',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_twitter',
                'value' => 'https://twitter.com/historiadotco',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'socmed_youtube',
                'value' => 'https://youtube.com',
                'type' => 'url',
                'group' => 'Social Media',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Home Page (Hero)
            [
                'key' => 'hero_title',
                'value' => 'Komunitas Historia Indonesia: Penjaga Memori Kolektif Bangsa',
                'type' => 'text',
                'group' => 'Home Page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Komunitas Historia Indonesia (KHI) telah membuktikan bahwa sejarah bukan sekadar pelajaran tentang masa lalu, tetapi fondasi penting dalam membangun nasionalisme dan ketahanan bangsa yang kokoh.',
                'type' => 'textarea',
                'group' => 'Home Page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_button_text',
                'value' => 'Bergabung Sekarang!',
                'type' => 'text',
                'group' => 'Home Page',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_image',
                'value' => 'images/img-hero.jpeg',
                'type' => 'image',
                'group' => 'Home Page',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // About
            [
                'key' => 'about_vision',
                'value' => 'Menjadi lembaga pelestarian sejarah dan budaya independen terkemuka di Asia Tenggara, menumbuhkan pemahaman mendalam bangsa sebagai pondasi patriotisme rakyat Indonesia yang berkepribadian mandiri.',
                'type' => 'textarea',
                'group' => 'About',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_mission',
                'value' => 'Mengemas pembelajaran nilai luhur sejarah secara rekreatif, edukatif, dan interaktif guna mendidik generasi muda aktif menjaga kelestarian cagar budaya serta warisan pusaka nasional.',
                'type' => 'textarea',
                'group' => 'About',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'about_description',
                'value' => '<p>Mengenal tata kelola keorganisasian, komitmen pelestarian nilai sejarah, serta lini masa pencapaian luar biasa Komunitas Historia Indonesia sejak tahun 2003.</p>',
                'type' => 'rich_text',
                'group' => 'About',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Legal
            [
                'key' => 'terms_of_service',
                'value' => '<p>Use the site respectfully and follow applicable laws and community guidelines.</p><p>Content may be updated, removed, or moderated when needed to keep the platform safe and useful.</p><p>By using the site, you agree to the terms and policies published here.</p>',
                'type' => 'rich_text',
                'group' => 'Legal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'privacy_policy',
                'value' => '<p>We only collect information needed to operate the site, manage accounts, and support community features.</p><p>We do not sell personal data. Shared data is limited to trusted service providers required for the app to function.</p><p>If you have privacy questions, contact the community team through the site\'s official channels.</p>',
                'type' => 'rich_text',
                'group' => 'Legal',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // E-Library
            [
                'key' => 'library_title',
                'value' => 'Digital Archive',
                'type' => 'text',
                'group' => 'Library',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'library_subtitle',
                'value' => 'Explore our extensive collection of digitized historical manuscripts, scholarly publications, and rare archival materials documenting the rich tapestry of Indonesian history.',
                'type' => 'textarea',
                'group' => 'Library',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Merchandise
            [
                'key' => 'merchandise_chip',
                'value' => 'Merchandise',
                'type' => 'text',
                'group' => 'Merchandise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'merchandise_title',
                'value' => 'KHI Store',
                'type' => 'text',
                'group' => 'Merchandise',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'merchandise_subtitle',
                'value' => 'Support historical preservation. Every purchase directly funds our educational programs and archive maintenance.',
                'type' => 'textarea',
                'group' => 'Merchandise',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Events
            [
                'key' => 'events_chip',
                'value' => 'Historical Gatherings',
                'type' => 'text',
                'group' => 'Events',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'events_title',
                'value' => 'KHI Community Events',
                'type' => 'text',
                'group' => 'Events',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'events_subtitle',
                'value' => 'Participate in our interactive walking tours, archive conservation workshops, history forums, and heritage conservation programs across Indonesia.',
                'type' => 'textarea',
                'group' => 'Events',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Aspirasi
            [
                'key' => 'aspirasi_chip',
                'value' => 'Suara Anggota KHI',
                'type' => 'text',
                'group' => 'Aspirasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'aspirasi_title',
                'value' => 'Aspirasi',
                'type' => 'text',
                'group' => 'Aspirasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'aspirasi_subtitle',
                'value' => 'Opini, esai, dan pemikiran mendalam mengenai pelestarian sejarah, identitas budaya, dan masa depan warisan Indonesia dari para anggota dan cendekiawan.',
                'type' => 'textarea',
                'group' => 'Aspirasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // News
            [
                'key' => 'news_chip',
                'value' => 'Historialita',
                'type' => 'text',
                'group' => 'News',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'news_title',
                'value' => 'Historialita',
                'type' => 'text',
                'group' => 'News',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'news_subtitle',
                'value' => 'Kurasi artikel, kabar komunitas, dan cerita sejarah dari Komunitas Historia Indonesia.',
                'type' => 'textarea',
                'group' => 'News',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('settings')->insert($settings);
    }
}
