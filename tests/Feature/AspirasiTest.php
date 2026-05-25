<?php

use App\Models\User;
use App\Models\Aspirasi;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use function Pest\Laravel\get;
use function Pest\Laravel\actingAs;

it('can render the aspirasi page', function () {
    $response = get('/aspirasi');
    $response->assertStatus(200);
    $response->assertSee('Aspirasi');
    $response->assertSee('Suara Anggota KHI');
});

it('allows filtering by category', function () {
    Volt::test('aspirasi')
        ->assertSet('categoryFilter', 'all')
        ->set('categoryFilter', 'cagar-budaya')
        ->assertSet('categoryFilter', 'cagar-budaya')
        ->set('categoryFilter', 'edukasi')
        ->assertSet('categoryFilter', 'edukasi');
});

it('renders the aspirasi detail page using the post-style layout', function () {
    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'cagar-budaya'],
        ['name' => 'Cagar Budaya', 'order' => 10]
    );

    $aspirasi = Aspirasi::create([
        'title' => 'Usulan Pemugaran Benteng Vredeburg',
        'body' => '<p>Detail usulan pemugaran untuk situs sejarah benteng peninggalan Belanda agar lebih terawat dan bersih.</p>',
        'slug' => 'usulan-pemugaran-benteng-vredeburg',
        'status' => 'PUBLISHED',
        'category_id' => $category->id,
    ]);

    get("/aspirasi/{$aspirasi->slug}")
        ->assertOk()
        ->assertSee('Usulan Pemugaran Benteng Vredeburg')
        ->assertSee('Detail usulan pemugaran untuk situs sejarah benteng peninggalan Belanda agar lebih terawat dan bersih.');
});

it('links aspirasi cards to the detail page', function () {
    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'cagar-budaya'],
        ['name' => 'Cagar Budaya', 'order' => 10]
    );

    $aspirasi = Aspirasi::create([
        'title' => 'Usulan Penataan Situs Sejarah',
        'body' => '<p>Detail singkat untuk memastikan kartu aspirasi menaut ke halaman detail.</p>',
        'slug' => 'usulan-penataan-situs-sejarah',
        'status' => 'PUBLISHED',
        'category_id' => $category->id,
    ]);

    get('/aspirasi')
        ->assertOk()
        ->assertSee(route('aspirasi.detail', ['slug' => $aspirasi->slug]));
});

it('links the featured aspirasi card to the detail page', function () {
    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'cagar-budaya'],
        ['name' => 'Cagar Budaya', 'order' => 10]
    );

    $aspirasi = Aspirasi::create([
        'title' => 'Esai Utama Aspirasi',
        'body' => '<p>Konten utama untuk memastikan kartu featured bisa diklik ke detail.</p>',
        'slug' => 'esai-utama-aspirasi',
        'status' => 'PUBLISHED',
        'category_id' => $category->id,
    ]);

    get('/aspirasi')
        ->assertOk()
        ->assertSeeHtml('href="' . route('aspirasi.detail', ['slug' => $aspirasi->slug]) . '"')
        ->assertSee('Esai Utama');
});

it('allows authenticated members to submit a new aspiration', function () {
    $user = User::create([
        'name' => 'Test Member',
        'email' => 'test-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'test-' . uniqid(),
    ]);

    actingAs($user);

    Volt::test('aspirasi')
        ->set('title', 'Usulan Pemugaran Benteng Vredeburg')
        ->set('categorySlug', 'cagar-budaya')
        ->set('body', 'Detail usulan pemugaran untuk situs sejarah benteng peninggalan Belanda agar lebih terawat dan bersih.')
        ->call('submitAspiration')
        ->assertHasNoErrors();

    // Verify it is created in the database
    $aspirasi = Aspirasi::where('title', 'Usulan Pemugaran Benteng Vredeburg')->first();
    expect($aspirasi)->not->toBeNull();
    expect($aspirasi->author_id)->toBe($user->id);
    expect($aspirasi->status)->toBe('PUBLISHED');
});

it('does not allow guest to submit a new aspiration', function () {
    Volt::test('aspirasi')
        ->set('title', 'Usulan Pemugaran Benteng Vredeburg')
        ->set('categorySlug', 'cagar-budaya')
        ->set('body', 'Detail usulan pemugaran untuk situs sejarah benteng peninggalan Belanda agar lebih terawat dan bersih.')
        ->call('submitAspiration');

    // Verify it is NOT created in the database
    $aspirasi = Aspirasi::where('title', 'Usulan Pemugaran Benteng Vredeburg')->first();
    expect($aspirasi)->toBeNull();
});

it('dashboard aspirasi create ignores tampered server owned fields', function (): void {
    $user = User::create([
        'name' => 'Aspirasi Owner',
        'email' => 'aspirasi-owner-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-owner-' . uniqid(),
    ]);

    $otherUser = User::create([
        'name' => 'Aspirasi Other',
        'email' => 'aspirasi-other-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-other-' . uniqid(),
    ]);

    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'edukasi'],
        ['name' => 'Edukasi', 'order' => 10]
    );

    actingAs($user);

    Volt::test('dashboard.aspirasi.create')
        ->set('data.title', 'Aspirasi Dashboard Aman')
        ->set('data.slug', 'aspirasi-dashboard-aman-' . uniqid())
        ->set('data.body', '<p>Konten aspirasi dashboard yang cukup panjang.</p>')
        ->set('data.category_id', $category->id)
        ->set('data.author_id', $otherUser->id)
        ->set('data.status', 'DRAFT')
        ->set('data.featured', true)
        ->call('create')
        ->assertHasNoErrors();

    $aspirasi = Aspirasi::where('title', 'Aspirasi Dashboard Aman')->firstOrFail();

    expect($aspirasi->author_id)->toBe($user->id)
        ->and($aspirasi->status)->toBe('PUBLISHED')
        ->and((bool) $aspirasi->featured)->toBeFalse();
});

it('dashboard aspirasi edit ignores tampered server owned fields', function (): void {
    $user = User::create([
        'name' => 'Aspirasi Editor',
        'email' => 'aspirasi-editor-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-editor-' . uniqid(),
    ]);

    $otherUser = User::create([
        'name' => 'Aspirasi Other Editor',
        'email' => 'aspirasi-other-editor-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-other-editor-' . uniqid(),
    ]);

    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'komunitas'],
        ['name' => 'Komunitas', 'order' => 10]
    );

    $aspirasi = Aspirasi::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Aspirasi Lama',
        'slug' => 'aspirasi-lama-' . uniqid(),
        'body' => '<p>Konten lama aspirasi.</p>',
        'status' => 'PUBLISHED',
        'featured' => false,
    ]);

    actingAs($user);

    Volt::test('dashboard.aspirasi.edit', ['id' => $aspirasi->id])
        ->set('data.title', 'Aspirasi Baru')
        ->set('data.slug', $aspirasi->slug)
        ->set('data.body', '<p>Konten baru aspirasi.</p>')
        ->set('data.category_id', $category->id)
        ->set('data.author_id', $otherUser->id)
        ->set('data.status', 'DRAFT')
        ->set('data.featured', true)
        ->call('update')
        ->assertHasNoErrors();

    $aspirasi->refresh();

    expect($aspirasi->title)->toBe('Aspirasi Baru')
        ->and($aspirasi->author_id)->toBe($user->id)
        ->and($aspirasi->status)->toBe('PUBLISHED')
        ->and((bool) $aspirasi->featured)->toBeFalse();
});

it('dashboard aspirasi edit blocks another users record', function (): void {
    $owner = User::create([
        'name' => 'Aspirasi Record Owner',
        'email' => 'aspirasi-record-owner-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-record-owner-' . uniqid(),
    ]);

    $attacker = User::create([
        'name' => 'Aspirasi Record Attacker',
        'email' => 'aspirasi-record-attacker-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-record-attacker-' . uniqid(),
    ]);

    $category = \Wave\Category::firstOrCreate(
        ['slug' => 'lainnya'],
        ['name' => 'Lainnya', 'order' => 10]
    );

    $aspirasi = Aspirasi::create([
        'author_id' => $owner->id,
        'category_id' => $category->id,
        'title' => 'Aspirasi Orang Lain',
        'slug' => 'aspirasi-orang-lain-' . uniqid(),
        'body' => '<p>Konten milik user lain.</p>',
        'status' => 'PUBLISHED',
        'featured' => false,
    ]);

    actingAs($attacker);

    expect(fn () => Volt::test('dashboard.aspirasi.edit', ['id' => $aspirasi->id]))
        ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

it('rate limits public aspirasi submissions', function (): void {
    $user = User::create([
        'name' => 'Aspirasi Rate Limit',
        'email' => 'aspirasi-rate-' . uniqid() . '@khi.org',
        'password' => bcrypt('secret123'),
        'username' => 'aspirasi-rate-' . uniqid(),
    ]);

    RateLimiter::clear("aspirasi-submit:user:{$user->id}");

    actingAs($user);

    for ($attempt = 0; $attempt < 6; $attempt++) {
        Volt::test('aspirasi')
            ->set('title', 'Usulan Rate Limit ' . $attempt)
            ->set('categorySlug', 'cagar-budaya')
            ->set('body', 'Detail usulan rate limit yang cukup panjang untuk lolos validasi.')
            ->call('submitAspiration')
            ->assertHasNoErrors();
    }

    Volt::test('aspirasi')
        ->set('title', 'Usulan Rate Limit Ditolak')
        ->set('categorySlug', 'cagar-budaya')
        ->set('body', 'Detail usulan rate limit yang cukup panjang untuk lolos validasi.')
        ->call('submitAspiration')
        ->assertHasErrors(['rate_limit']);
});
