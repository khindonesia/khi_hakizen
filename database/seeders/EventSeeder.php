<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $authorId = $admin ? $admin->id : 1;

        $events = [
            [
                'title' => 'Jelajah Sejarah Kota Tua Jakarta',
                'body' => '<p>Bergabunglah bersama Komunitas Historia Indonesia (KHI) dalam petualangan menyusuri lorong waktu di Kota Tua Jakarta! Kita akan mengunjungi berbagai situs bersejarah seperti Museum Fatahillah, Toko Merah, Jembatan Kota Intan, dan Sunda Kelapa.</p><p>Acara ini bertujuan untuk memperkenalkan sejarah kolonial Batavia dan peran penting pelabuhan Sunda Kelapa dalam jalur perdagangan dunia.</p><p><strong>Fasilitas:</strong> Tiket masuk museum, pemandu sejarah (historian), makan siang, dan merchandise eksklusif KHI.</p>',
                'image' => 'demo/event-kotatua.jpg',
                'location' => 'Kota Tua Jakarta, Special Capital Region of Jakarta',
                'status' => 'PUBLISHED',
                'type' => 'PAID',
                'price' => 150000.00,
                'start_datetime' => now()->addDays(5)->setHour(8)->setMinute(0)->setSecond(0),
                'end_datetime' => now()->addDays(5)->setHour(14)->setMinute(0)->setSecond(0),
                'meta_description' => 'Jelajah Sejarah Kota Tua Jakarta bersama Komunitas Historia Indonesia (KHI). Jelajahi keindahan Batavia lama.',
                'meta_keywords' => 'kota tua, jakarta, sejarah, batavia, khi, komunitas historia indonesia',
            ],
            [
                'title' => 'Webinar Sejarah Nusantara: Kejayaan Sriwijaya & Majapahit',
                'body' => '<p>Bagaimana Sriwijaya dan Majapahit menguasai maritim Nusantara dan mempengaruhi Asia Tenggara? Temukan jawabannya dalam webinar eksklusif ini bersama para ahli arkeologi dan sejarah nasional.</p><p>Acara ini akan mengupas tuntas struktur politik, kebudayaan, perdagangan maritim, dan faktor-faktor kemunduran kedua imperium besar ini.</p><p>Acara akan dilangsungkan secara daring via Zoom Meeting.</p>',
                'image' => 'demo/event-webinar.jpg',
                'location' => 'Daring via Zoom Meeting',
                'status' => 'PUBLISHED',
                'type' => 'FREE',
                'price' => 0,
                'start_datetime' => now()->addDays(15)->setHour(19)->setMinute(0)->setSecond(0),
                'end_datetime' => now()->addDays(15)->setHour(21)->setMinute(30)->setSecond(0),
                'meta_description' => 'Webinar Sejarah Nusantara mengupas tuntas kejayaan kemaritiman kerajaan Sriwijaya dan Majapahit.',
                'meta_keywords' => 'webinar, sejarah nusantara, sriwijaya, majapahit, maritim nusantara',
            ],
            [
                'title' => 'Historical Walk: Jejak Kebudayaan Tionghoa di Glodok',
                'body' => '<p>Menelusuri akulturasi budaya Tionghoa dan Betawi di kawasan Pecinan tertua di Jakarta. Kita akan mengunjungi Vihara Dharma Bhakti, Gereja Santa Maria de Fatima yang berarsitektur Tionghoa, serta menikmati kuliner legendaris Glodok Pancoran.</p><p>Rasakan kehangatan toleransi budaya dan kisah-kisah perjuangan komunitas Tionghoa di Batavia abad ke-18.</p>',
                'image' => 'demo/event-glodok.jpg',
                'location' => 'Glodok Pecinan, Jakarta Barat',
                'status' => 'PUBLISHED',
                'type' => 'PAID',
                'price' => 75000.00,
                'start_datetime' => now()->subDays(10)->setHour(8)->setMinute(0)->setSecond(0),
                'end_datetime' => now()->subDays(10)->setHour(13)->setMinute(0)->setSecond(0),
                'meta_description' => 'Menyusuri jejak akulturasi kebudayaan Tionghoa dan Betawi di kawasan Glodok Pancoran.',
                'meta_keywords' => 'glodok, pecinan, akulturasi, tionghoa, betawi, walking tour',
            ],
            [
                'title' => 'Pameran Dokumen & Foto Kuno: Wajah Jakarta Tempo Doeloe',
                'body' => '<p>Komunitas Historia Indonesia bekerja sama dengan Arsip Nasional menyelenggarakan pameran dokumen asli dan galeri foto langka Jakarta periode 1880 - 1940.</p><p>Lihat secara langsung surat kabar kuno, peta topografi Batavia lama, dan dokumentasi sosial masyarakat pada masa kolonial.</p><p>Pameran terbuka untuk umum dan gratis di Galeri KHI.</p>',
                'image' => 'demo/event-pameran.jpg',
                'location' => 'Galeri KHI, Jakarta Selatan',
                'status' => 'PUBLISHED',
                'type' => 'FREE',
                'price' => 0,
                'start_datetime' => now()->subDays(2)->setHour(9)->setMinute(0)->setSecond(0),
                'end_datetime' => now()->addDays(3)->setHour(17)->setMinute(0)->setSecond(0),
                'meta_description' => 'Pameran dokumentasi kuno dan foto langka perjalanan sejarah Jakarta tempo doeloe.',
                'meta_keywords' => 'pameran foto, jakarta kuno, batavia tempo doeloe, arsip sejarah',
            ]
        ];

        foreach ($events as $evt) {
            Event::updateOrCreate(
                ['slug' => Str::slug($evt['title'])],
                [
                    'author_id' => $authorId,
                    'title' => $evt['title'],
                    'seo_title' => $evt['title'],
                    'body' => $evt['body'],
                    'image' => $evt['image'],
                    'location' => $evt['location'],
                    'meta_description' => $evt['meta_description'],
                    'meta_keywords' => $evt['meta_keywords'],
                    'status' => $evt['status'],
                    'type' => $evt['type'],
                    'price' => $evt['price'],
                    'start_datetime' => $evt['start_datetime'],
                    'end_datetime' => $evt['end_datetime'],
                ]
            );
        }
    }
}
