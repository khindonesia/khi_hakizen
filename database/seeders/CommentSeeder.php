<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Post;
use App\Models\Aspirasi;
use App\Models\Event;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $posts = Post::all();
        $aspirasis = Aspirasi::all();
        $events = Event::all();

        // Sample comments list
        $commentsData = [
            'Post' => [
                [
                    'body' => 'Artikel yang sangat komprehensif. Penggunaan sumber primer dari abad ke-19 memberikan kedalaman yang jarang ditemukan pada literatur populer. Apakah ada rencana untuk mendigitalisasi sisa manuskrip tersebut?',
                    'replies' => [
                        'Terima kasih, Ayu. Ya, proses digitalisasi fase kedua sedang berlangsung. Kami menargetkan untuk mempublikasikan seluruh koleksi resolusi tinggi pada kuartal depan.',
                        'Sangat tidak sabar menunggu publikasinya! Ini akan sangat membantu riset disertasi saya.'
                    ]
                ],
                [
                    'body' => 'Observasi mengenai etika preservasi di paragraf ketiga sangat tepat sasaran. Seringkali kita melupakan konteks kultural saat memindahkan artefak ke ruang pamer modern. Sebuah pengingat yang baik untuk komunitas sejarawan.',
                    'replies' => []
                ]
            ],
            'Aspirasi' => [
                [
                    'body' => 'Sangat setuju dengan aspirasi ini. Kita butuh wadah yang lebih inklusif untuk menyuarakan pendapat anggota.',
                    'replies' => [
                        'Betul sekali, semoga pengurus segera menindaklanjuti hal ini.',
                    ]
                ],
                [
                    'body' => 'Apakah ada rencana untuk mengadakan forum diskusi terbuka mengenai poin-poin usulan ini?',
                    'replies' => []
                ]
            ],
            'Event' => [
                [
                    'body' => 'Topik yang sangat menarik! Saya sudah mendaftar dan tidak sabar untuk berdiskusi dengan para narasumber.',
                    'replies' => [
                        'Sampai jumpa di lokasi acara!',
                    ]
                ],
                [
                    'body' => 'Apakah rekaman acara ini nantinya akan dibagikan kepada peserta yang berhalangan hadir secara langsung?',
                    'replies' => [
                        'Ya, semua sesi akan direkam dan diunggah ke perpustakaan digital KHI setelah acara selesai.'
                    ]
                ]
            ]
        ];

        // Seeding helper
        $seedComments = function ($items, string $type) use ($users, $commentsData) {
            foreach ($items as $item) {
                $templates = $commentsData[$type] ?? [];
                foreach ($templates as $template) {
                    // Create parent comment
                    $parentComment = Comment::create([
                        'user_id' => $users->random()->id,
                        'body' => $template['body'],
                        'commentable_id' => $item->id,
                        'commentable_type' => get_class($item),
                    ]);

                    // Attach random likes to parent comment
                    $likeCount = rand(0, min(15, $users->count()));
                    if ($likeCount > 0) {
                        $parentComment->likes()->attach($users->random($likeCount)->pluck('id'));
                    }

                    // Create replies if any
                    foreach ($template['replies'] as $replyBody) {
                        $replyComment = Comment::create([
                            'user_id' => $users->random()->id,
                            'parent_id' => $parentComment->id,
                            'body' => $replyBody,
                            'commentable_id' => $item->id,
                            'commentable_type' => get_class($item),
                        ]);

                        // Attach random likes to reply comment
                        $replyLikeCount = rand(0, min(8, $users->count()));
                        if ($replyLikeCount > 0) {
                            $replyComment->likes()->attach($users->random($replyLikeCount)->pluck('id'));
                        }
                    }
                }
            }
        };

        // Seed posts, aspirasis, and events comments
        $seedComments($posts, 'Post');
        $seedComments($aspirasis, 'Aspirasi');
        $seedComments($events, 'Event');
    }
}
