<?php

namespace Database\Seeders;

use App\Models\Aspirasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AspirasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $authorId = $admin ? $admin->id : 1;

        $aspirasis = [
            [
                'title' => 'Penyelamatan dan Digitalisasi Manuskrip Nusantara',
                'excerpt' => 'Mendorong upaya masif penyelamatan manuskrip kuno milik warga untuk didigitalisasi agar tidak lekang oleh waktu.',
                'body' => '<p>Banyak sekali manuskrip sejarah bernilai tinggi yang saat ini masih disimpan secara pribadi oleh warga di berbagai daerah. Tanpa perawatan yang memadai, manuskrip-manuskrip berbahan daun lontar maupun kertas kuno ini terancam rusak digigit serangga atau lapuk karena kelembapan udara.</p><p>Kami menyarankan adanya gerakan sukarela kolaboratif antara komunitas pencinta sejarah, akademisi, dan pemerintah untuk mendata, merawat, serta melakukan digitalisasi penuh atas dokumen-dokumen berharga ini sehingga dapat diakses oleh publik luas untuk kepentingan riset dan edukasi.</p>',
                'status' => 'PUBLISHED',
                'featured' => true,
                'meta_description' => 'Aspirasi penyelamatan dan digitalisasi naskah kuno nusantara yang terancam punah.',
                'meta_keywords' => 'manuskrip, naskah kuno, digitalisasi, sejarah nusantara, arsip nasional',
            ],
            [
                'title' => 'Revitalisasi Stasiun Kereta Non-Aktif Sebagai Pusat Komunitas Seni',
                'excerpt' => 'Mengusulkan pengaktifan kembali stasiun perkeretaapian peninggalan kolonial yang mati sebagai ruang publik kreatif.',
                'body' => '<p>Indonesia memiliki ratusan stasiun kereta api non-aktif peninggalan era kolonial Belanda yang tersebar di pulau Jawa dan Sumatera. Sebagian besar bangunan ini telantar dan beralih fungsi menjadi area kumuh.</p><p>Kami beraspirasi agar PT KAI bekerja sama dengan komunitas lokal memanfaatkan bangunan cagar budaya ini sebagai galeri seni, perpustakaan sejarah, atau ruang kreatif pemuda tanpa merusak arsitektur aslinya. Hal ini akan menghidupkan kembali denyut ekonomi sekaligus merawat warisan perkeretaapian kita.</p>',
                'status' => 'PUBLISHED',
                'featured' => false,
                'meta_description' => 'Revitalisasi arsitektur stasiun kereta api kuno menjadi ruang kreatif komunitas.',
                'meta_keywords' => 'cagar budaya, stasiun kereta, kolonial, revitalisasi, ruang kreatif',
            ],
            [
                'title' => 'Penyediaan Panduan Audio Multi-Bahasa di Seluruh Museum Sejarah',
                'excerpt' => 'Meningkatkan pelayanan museum di Indonesia melalui penyediaan pemandu audio digital yang ramah bagi wisatawan asing maupun lokal.',
                'body' => '<p>Untuk menarik lebih banyak minat generasi muda dan wisatawan asing berkunjung ke museum-museum di Indonesia, penyajian informasi harus dikemas lebih modern. Papan informasi statis di museum sering kali terlalu padat dan hanya tersedia dalam bahasa Indonesia yang kaku.</p><p>Penggunaan QR Code yang terhubung ke panduan audio multi-bahasa (Indonesia, Inggris, Jepang, Mandarin) akan sangat membantu pengunjung memahami konteks di balik artefak sejarah yang dipamerkan.</p>',
                'status' => 'PENDING',
                'featured' => false,
                'meta_description' => 'Aspirasi penyediaan pemandu audio multi-bahasa digital untuk seluruh museum sejarah.',
                'meta_keywords' => 'museum, audio guide, digitalisasi museum, pariwisata sejarah',
            ]
        ];

        foreach ($aspirasis as $asp) {
            Aspirasi::updateOrCreate(
                ['slug' => Str::slug($asp['title'])],
                [
                    'author_id' => $authorId,
                    'category_id' => 1, // Marketing/General
                    'title' => $asp['title'],
                    'seo_title' => $asp['title'],
                    'excerpt' => $asp['excerpt'],
                    'body' => $asp['body'],
                    'image' => 'demo/aspirasi-' . Str::slug($asp['title']) . '.jpg',
                    'meta_description' => $asp['meta_description'],
                    'meta_keywords' => $asp['meta_keywords'],
                    'status' => $asp['status'],
                    'featured' => $asp['featured'],
                ]
            );
        }
    }
}
