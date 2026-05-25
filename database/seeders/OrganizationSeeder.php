<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Asep Kambali',
                'avatar' => 'demo/org-asep-kambali.jpg',
                'position' => 'Founder & Ketua Umum KHI',
                'description' => 'Asep Kambali adalah sejarawan, akademisi, dan aktivis pelestarian sejarah Indonesia. Ia mendirikan Komunitas Historia Indonesia (KHI) pada tahun 2003 untuk mempopulerkan sejarah nasional kepada generasi muda secara interaktif.',
                'facebook_url' => 'https://facebook.com/asepkambali',
                'instagram_url' => 'https://instagram.com/asepkambali',
                'linkedin_url' => 'https://linkedin.com/in/asepkambali',
                'twitter_url' => 'https://twitter.com/asepkambali',
            ],
            [
                'name' => 'Prof. Dr. Susanto Zuhdi',
                'avatar' => 'demo/org-susanto-zuhdi.jpg',
                'position' => 'Dewan Pembina / Guru Besar Sejarah UI',
                'description' => 'Guru besar Departemen Sejarah Fakultas Ilmu Pengetahuan Budaya Universitas Indonesia. Beliau membimbing riset historis dan verifikasi kebenaran edukasi sejarah di KHI.',
                'facebook_url' => null,
                'instagram_url' => 'https://instagram.com/susantozuhdi',
                'linkedin_url' => null,
                'twitter_url' => null,
            ],
            [
                'name' => 'Kartika Chandra, M.A.',
                'avatar' => 'demo/org-kartika-chandra.jpg',
                'position' => 'Sekretaris Jenderal',
                'description' => 'Kartika mengelola operasional harian, administrasi keanggotaan, serta koordinasi hubungan masyarakat KHI dengan berbagai kementerian dan mitra internasional.',
                'facebook_url' => 'https://facebook.com/kartikachandra',
                'instagram_url' => 'https://instagram.com/kartikachandra',
                'linkedin_url' => 'https://linkedin.com/in/kartikachandra',
                'twitter_url' => null,
            ]
        ];

        foreach ($members as $m) {
            Organization::updateOrCreate(
                ['name' => $m['name']],
                [
                    'avatar' => $m['avatar'],
                    'position' => $m['position'],
                    'description' => $m['description'],
                    'facebook_url' => $m['facebook_url'],
                    'instagram_url' => $m['instagram_url'],
                    'linkedin_url' => $m['linkedin_url'],
                    'twitter_url' => $m['twitter_url'],
                ]
            );
        }
    }
}
