<?php

namespace Database\Seeders;

use App\Models\Ebook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ebooks = [
            [
                'title' => 'Sejarah Singkat Kerajaan Maritim di Nusantara',
                'cover_image' => 'demo/ebook-maritim.jpg',
                'author' => 'Ahmad Mansur, M.Hum.',
                'description' => 'Sebuah catatan komprehensif mengenai kejayaan maritim Nusantara mulai dari Kerajaan Sriwijaya, Majapahit, hingga Kesultanan Samudera Pasai dan bagaimana mereka mendominasi jalur sutra laut.',
                'ebook_file' => 'demo/ebook-maritim.pdf',
                'status' => 'PUBLISHED',
            ],
            [
                'title' => 'Menyusuri Batavia: Panduan Wisata Sejarah Kota Tua Jakarta',
                'cover_image' => 'demo/ebook-batavia.jpg',
                'author' => 'Pradipta Ramadhan',
                'description' => 'Buku saku digital yang mengulas rute jalan kaki terbaik di Kota Tua Jakarta lengkap dengan penjelasan historis setiap sudut gedung dan tips memotret bangunan bersejarah.',
                'ebook_file' => 'demo/ebook-batavia.pdf',
                'status' => 'PUBLISHED',
            ],
            [
                'title' => 'Arsitektur & Simbolisme Candi-Candi Nusantara',
                'cover_image' => 'demo/ebook-candi.jpg',
                'author' => 'Dr. Rian Sugiyanto',
                'description' => 'Analisis mendalam mengenai bentuk fisik, relief, serta filosofi keagamaan yang terpahat pada candi-candi megah peninggalan masa Klasik Hindu-Buddha di Indonesia.',
                'ebook_file' => 'demo/ebook-candi.pdf',
                'status' => 'PUBLISHED',
            ]
        ];

        foreach ($ebooks as $eb) {
            Ebook::updateOrCreate(
                ['slug' => Str::slug($eb['title'])],
                [
                    'title' => $eb['title'],
                    'cover_image' => $eb['cover_image'],
                    'author' => $eb['author'],
                    'description' => $eb['description'],
                    'ebook_file' => $eb['ebook_file'],
                    'status' => $eb['status'],
                ]
            );
        }
    }
}
