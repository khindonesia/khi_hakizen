<?php

use App\Models\User;
use App\Models\Event;
use App\Services\XenditInvoiceGateway;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;

function createEventCheckoutUser(string $prefix = 'evt-checkout'): User
{
    return User::create([
        'name' => 'Event Checkout Tester',
        'email' => $prefix . '-' . uniqid() . '@example.com',
        'password' => Hash::make('secret123'),
        'username' => $prefix . '-' . uniqid(),
        'verified' => 1,
    ]);
}

function createTestEvent(string $type = 'PAID', float $price = 150000.00): Event
{
    $author = createEventCheckoutUser('author');
    return Event::create([
        'author_id' => $author->id,
        'title' => 'Test Event ' . uniqid(),
        'slug' => 'test-event-' . uniqid(),
        'body' => 'Test event body description',
        'location' => 'Jakarta, Indonesia',
        'type' => $type,
        'price' => $price,
        'start_datetime' => now()->addDays(2),
        'end_datetime' => now()->addDays(2)->addHours(3),
    ]);
}

function fakeEventCheckoutDependencies(): void
{
    config()->set('services.xendit.secret_key', 'xnd_test_key');
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    app()->bind(XenditInvoiceGateway::class, fn () => new class extends XenditInvoiceGateway {
        public function __construct()
        {
        }

        public function createEventInvoice(string $externalId, float $amount, string $description, User $user, array $items, string $successUrl, string $failureUrl): array
        {
            return [
                'id' => 'inv-evt-test-' . rand(100, 999),
                'invoice_url' => 'https://pay.test/invoice-evt/' . $externalId,
            ];
        }
    });
}

it('requires authentication to access the event checkout page', function (): void {
    $event = createTestEvent();
    $response = $this->get('/events/checkout?event=' . $event->id);
    $response->assertRedirect('/login');
});

it('requires a valid event parameter', function (): void {
    $user = createEventCheckoutUser('auth');
    $response = $this->actingAs($user)->get('/events/checkout');
    $response->assertNotFound();
});

it('requires a phone_number when creating checkout invoice', function (): void {
    fakeEventCheckoutDependencies();

    $user = createEventCheckoutUser('buyer');
    $event = createTestEvent('PAID', 150000);

    $response = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone_number']);
});

it('returns a clear error when Xendit API key is missing for paid events', function (): void {
    config()->set('services.xendit.secret_key', null);

    $user = createEventCheckoutUser('buyer');
    $event = createTestEvent('PAID', 150000);

    $response = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '08123456789',
    ]);

    $response->assertStatus(500)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Xendit API key belum dikonfigurasi. Set XENDIT_SECRET_KEY di .env lalu jalankan php artisan config:clear.');
});

it('creates a Xendit invoice for a PAID event, stores phone number, and returns the invoice URL', function (): void {
    fakeEventCheckoutDependencies();

    $user = createEventCheckoutUser('buyer');
    $event = createTestEvent('PAID', 150000);

    $response = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '08987654321',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJson([
            'status' => 'success',
            'message' => 'Invoice created successfully'
        ]);

    $data = $response->json('data');
    expect($data['invoice_url'])->toStartWith('https://pay.test/invoice-evt/EVT-');
    expect($user->refresh()->phone_number)->toBe('08987654321');

    // Assert pivot table state
    $pivot = DB::table('event_user')
        ->where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

    expect($pivot)->not->toBeNull()
        ->and($pivot->status)->toBe('pending')
        ->and($pivot->payment_status)->toBe('pending')
        ->and((float)$pivot->amount)->toBe(150000.00)
        ->and($pivot->external_id)->toStartWith('EVT-')
        ->and($pivot->payment_url)->toBe($data['invoice_url']);
});

it('processes FREE event checkout instantly, saving registration directly as active', function (): void {
    fakeEventCheckoutDependencies();

    $user = createEventCheckoutUser('free-buyer');
    $event = createTestEvent('FREE', 0);

    $response = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '081122334455',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.invoice_url', route('dashboard.events') . '?payment_status=success');

    expect($user->refresh()->phone_number)->toBe('081122334455');

    // Assert registration is immediately active and free
    $pivot = DB::table('event_user')
        ->where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->first();

    expect($pivot)->not->toBeNull()
        ->and($pivot->status)->toBe('active')
        ->and($pivot->payment_status)->toBe('free')
        ->and((float)$pivot->amount)->toBe(0.00);
});

it('rejects registration if the user is already active for that event', function (): void {
    fakeEventCheckoutDependencies();

    $user = createEventCheckoutUser('duplicate-buyer');
    $event = createTestEvent('PAID', 150000);

    // Seed active registration
    DB::table('event_user')->insert([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'active',
        'payment_status' => 'paid',
        'amount' => 150000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson('/api/events/checkout/create-invoice', [
        'event_id' => $event->id,
        'phone_number' => '08123456789',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Anda sudah terdaftar untuk event ini.');
});

it('handles Xendit webhook callback for event registrations', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createEventCheckoutUser('webhook-buyer');
    $event = createTestEvent('PAID', 150000);
    $externalId = 'EVT-' . $event->id . '-' . $user->id . '-' . time();

    // Insert pending registration
    DB::table('event_user')->insert([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'amount' => 150000,
        'external_id' => $externalId,
        'invoice_id' => 'inv-test-123',
        'payment_url' => 'https://pay.test/inv-123',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Test PAID Callback
    $response = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => $externalId,
            'status' => 'PAID',
        ]);

    $response->assertOk()
        ->assertJsonPath('status', 'success');

    $pivot = DB::table('event_user')->where('external_id', $externalId)->first();
    expect($pivot->status)->toBe('active')
        ->and($pivot->payment_status)->toBe('paid');

    // Test EXPIRED Callback
    $userExpired = createEventCheckoutUser('webhook-expired-buyer');
    $eventExpired = createTestEvent('PAID', 150000);
    $externalIdExpired = 'EVT-' . $eventExpired->id . '-' . $userExpired->id . '-expired-' . time();
    DB::table('event_user')->insert([
        'event_id' => $eventExpired->id,
        'user_id' => $userExpired->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'amount' => 150000,
        'external_id' => $externalIdExpired,
        'invoice_id' => 'inv-test-expired',
        'payment_url' => 'https://pay.test/inv-expired',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $responseExpired = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => $externalIdExpired,
            'status' => 'EXPIRED',
        ]);

    $responseExpired->assertOk()
        ->assertJsonPath('status', 'success');

    $pivotExpired = DB::table('event_user')->where('external_id', $externalIdExpired)->first();
    expect($pivotExpired->status)->toBe('cancelled')
        ->and($pivotExpired->payment_status)->toBe('expired');
});

it('does not downgrade an already paid event registration from an expired webhook', function (): void {
    config()->set('services.xendit.webhook_secret', 'webhook-secret');

    $user = createEventCheckoutUser('webhook-paid-buyer');
    $event = createTestEvent('PAID', 150000);
    $externalId = 'EVT-' . $event->id . '-' . $user->id . '-paid-' . time();

    DB::table('event_user')->insert([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => 'active',
        'payment_status' => 'paid',
        'amount' => 150000,
        'external_id' => $externalId,
        'invoice_id' => 'inv-test-paid',
        'payment_url' => 'https://pay.test/inv-paid',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->withHeader('x-callback-token', 'webhook-secret')
        ->postJson('/api/xendit/callback', [
            'external_id' => $externalId,
            'status' => 'EXPIRED',
        ]);

    $response->assertOk()->assertJsonPath('status', 'success');

    $pivot = DB::table('event_user')->where('external_id', $externalId)->first();
    expect($pivot->status)->toBe('active')
        ->and($pivot->payment_status)->toBe('paid');
});
