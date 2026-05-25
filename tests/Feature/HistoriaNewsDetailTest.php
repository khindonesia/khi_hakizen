<?php

use App\Models\User;
use Wave\Category;
use Wave\Post;
use function Pest\Laravel\get;

it('renders historia news article detail as a full article page', function (): void {
    $user = User::create([
        'name' => 'Historia Writer',
        'email' => 'historia-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'historia-' . uniqid(),
        'verified' => 1,
    ]);

    $category = Category::firstOrCreate(
        ['slug' => 'historia-news'],
        ['name' => 'Historia News', 'order' => 10]
    );

    $post = Post::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Jejak Baru di Kota Tua',
        'body' => '<p>Artikel ini menjelaskan detail pembaruan komunitas dan catatan sejarah terbaru.</p>',
        'slug' => 'jejak-baru-di-kota-tua',
        'status' => 'PUBLISHED',
    ]);

    get("/historia-news/{$post->slug}")
        ->assertOk()
        ->assertSee('Jejak Baru di Kota Tua')
        ->assertSee('Artikel ini menjelaskan detail pembaruan komunitas dan catatan sejarah terbaru.');
});
