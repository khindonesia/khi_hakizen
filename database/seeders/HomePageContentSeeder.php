<?php

namespace Database\Seeders;

use App\Models\HomePageContent;
use App\Models\HomeAchievement;
use Illuminate\Database\Seeder;

class HomePageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing
        \DB::table('home_achievements')->delete();
        \DB::table('home_page_contents')->delete();

        $content = HomePageContent::create([
            'hero_title' => 'Menembus Batas Waktu, Melestarikan Memori Bangsa',
            'hero_subtitle' => 'Komunitas Historia Indonesia (KHI) hadir sebagai wadah inovatif bagi generasi muda untuk mengeksplorasi warisan sejarah dan cagar budaya Nusantara secara interaktif, mendalam, dan menyenangkan.',
            'hero_image' => 'demo/hero-sejarah.jpg',
            'hero_button_text' => 'Gabung Komunitas',
            'org_name' => 'Komunitas Historia Indonesia',
            'org_acronym' => 'KHI',
            'org_description' => 'Didirikan pada tahun 2003, Komunitas Historia Indonesia (KHI) berkomitmen membangun kesadaran sejarah dan kepedulian cagar budaya di kalangan masyarakat Indonesia, khususnya generasi muda. Kami percaya bahwa memahami masa lalu adalah kunci untuk mengukir masa depan bangsa yang tangguh dan bermartabat.',
            'leader_name' => 'Asep Kambali',
            'leader_position' => 'Pendiri Komunitas Historia Indonesia',
            'leader_image' => 'demo/leader-asep.jpg',
            'leader_bio' => 'Seorang sejarawan publik dan pelopor gerakan pemasyarakatan sejarah yang ramah kaum muda. Dedikasinya membawa KHI memenangkan berbagai penghargaan nasional maupun internasional atas kontribusinya dalam pelestarian nilai kepahlawanan.',
        ]);

        $achievements = [
            '22+ Tahun Konsisten Mengedukasi Sejarah Nusantara',
            '100,000+ Anggota Aktif & Relawan Tersebar di Seluruh Indonesia',
            '1,200+ Kegiatan Jelajah Sejarah & Kunjungan Museum Sukses Terlaksana',
            'Mitra Resmi Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi',
        ];

        foreach ($achievements as $index => $achievementTitle) {
            HomeAchievement::create([
                'home_page_content_id' => $content->id,
                'achievement_title' => $achievementTitle,
                'display_order' => $index,
            ]);
        }
    }
}
