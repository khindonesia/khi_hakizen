<?php

// tests/Feature/RouteResponseTest.php

use function Pest\Laravel\get;

beforeEach(function () {
    $this->artisan('db:seed');
});

it('responds with 200 for all routes', function (string $route) {
    $response = get($route);
    $response->assertStatus(200);
})->with('routes');

test('responds with 200 for all auth routes', function ($url) {
    try {
        $user = \App\Models\User::first() ?: \App\Models\User::create([
            'id' => 1,
            'name' => 'Wave Admin',
            'email' => 'admin@admin.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'verified' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get($url);

        if ($response->status() !== 200) {
            dump("Failing URL: " . $url . " (Status: " . $response->status() . ")");
        }

        $response->assertStatus(200);
    } catch (\Throwable $e) {
        dump("Exception for URL " . $url . ": " . $e->getMessage());
        throw $e;
    }
})->with('authroutes');
