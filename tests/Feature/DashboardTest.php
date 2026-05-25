<?php

use Wave\Changelog;
use App\Models\User;
use App\Models\Event;

it('dashboard loads successfully with upcoming events', function () {
    // Ensure we have a user
    $user = User::first() ?: User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Jelajah Sejarah Kota Tua Jakarta');
    $response->assertSee('Detail');
    $response->assertSee('showModal');
});

it('dashboard events page loads successfully with search and filter tabs', function () {
    $user = User::first() ?: User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard/events');

    $response->assertStatus(200);
    $response->assertSee('KHI Community Events');
    $response->assertSee('All Events');
    $response->assertSee('Upcoming');
    $response->assertSee('Ongoing');
    $response->assertSee('Past Events');
});

it('dashboard does not show the changelog release banner', function () {
    $user = User::first() ?: User::factory()->create();

    $latestChangelog = Changelog::create([
        'title' => 'Test Release Banner',
        'description' => 'This release banner should not be visible on the dashboard.',
        'body' => '<p>Test banner body.</p>',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertDontSee($latestChangelog->title);
    $response->assertDontSee($latestChangelog->description);
});

it('dashboard only shows owned or registered events', function () {
    // 1. Create a user (User A) and another user (User B) manually with proper attributes
    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@example.com',
        'username' => 'usera',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);
    $userB = User::create([
        'name' => 'User B',
        'email' => 'userb@example.com',
        'username' => 'userb',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);

    // 2. Create Event 1 owned by User A
    $eventOwnedByUserA = Event::create([
        'author_id' => $userA->id,
        'title' => 'Event Owned By User A',
        'body' => 'Event description here.',
        'slug' => 'event-owned-by-user-a',
        'status' => 'PUBLISHED',
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    // 3. Create Event 2 owned by User B (User A is NOT registered initially)
    $eventOwnedByUserB = Event::create([
        'author_id' => $userB->id,
        'title' => 'Event Owned By User B',
        'body' => 'Another event description.',
        'slug' => 'event-owned-by-user-b',
        'status' => 'PUBLISHED',
        'start_datetime' => now()->addDays(3),
        'end_datetime' => now()->addDays(3)->addHours(2),
    ]);

    // 4. Act as User A and view the dashboard events page
    $response = $this->actingAs($userA)->get('/dashboard/events');

    $response->assertStatus(200);
    // User A should see their owned event
    $response->assertSee('Event Owned By User A');
    // User A should NOT see User B's event since they aren't registered for it
    $response->assertDontSee('Event Owned By User B');

    // 5. Now register User A for Event 2 (User B's event)
    $eventOwnedByUserB->users()->attach($userA->id);

    // 6. Act as User A again and view the dashboard events page
    $response = $this->actingAs($userA)->get('/dashboard/events');

    $response->assertStatus(200);
    // User A should see their owned event
    $response->assertSee('Event Owned By User A');
    // User A should now also see Event 2 (owned by User B) because they are registered for it
    $response->assertSee('Event Owned By User B');
});

it('guest cannot access event ticket page', function () {
    $user = User::create([
        'name' => 'User Guest',
        'email' => 'guest@example.com',
        'username' => 'userguest',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);
    $event = Event::create([
        'author_id' => $user->id,
        'title' => 'Cetak Tiket Event Test',
        'body' => 'Event description.',
        'slug' => 'cetak-tiket-event-test',
        'status' => 'PUBLISHED',
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    $response = $this->get("/dashboard/events/{$event->id}/ticket");

    $response->assertRedirect('/login');
});

it('registered user can access event ticket page', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'username' => 'johndoe',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);
    
    $event = Event::create([
        'author_id' => $user->id,
        'title' => 'Cetak Tiket Event Test',
        'body' => 'Event description.',
        'slug' => 'cetak-tiket-event-test-2',
        'status' => 'PUBLISHED',
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    // Registered user with active status
    $event->users()->attach($user->id, [
        'status' => 'active',
        'payment_status' => 'free',
        'amount' => 0,
        'external_id' => 'EVT-TEST-123456',
    ]);

    $response = $this->actingAs($user)->get("/dashboard/events/{$event->id}/ticket");

    $response->assertStatus(200);
    $response->assertSee('E-Tiket: Cetak Tiket Event Test');
    $response->assertSee('John Doe');
    $response->assertSee('EVT-TEST-123456');
});

it('unregistered user receives 403 when accessing ticket page', function () {
    $user = User::create([
        'name' => 'User Unregistered',
        'email' => 'unregistered@example.com',
        'username' => 'unregistered',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);
    $eventOwner = User::create([
        'name' => 'Event Owner',
        'email' => 'owner@example.com',
        'username' => 'owner',
        'password' => bcrypt('password'),
        'avatar' => 'users/default.png',
        'verified' => 1,
    ]);
    
    $event = Event::create([
        'author_id' => $eventOwner->id,
        'title' => 'Cetak Tiket Event Test',
        'body' => 'Event description.',
        'slug' => 'cetak-tiket-event-test-3',
        'status' => 'PUBLISHED',
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(2),
    ]);

    $response = $this->actingAs($user)->get("/dashboard/events/{$event->id}/ticket");

    $response->assertStatus(403);
});

