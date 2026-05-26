<?php

use App\Models\User;
use Livewire\Volt\Volt;
use Database\Seeders\RolesTableSeeder;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolesTableSeeder::class);
});

it('can render the join membership page', function () {
    $response = get('/join');
    $response->assertStatus(200);
    $response->assertSee('Become a Keeper of History');
});

it('can register a new member and show waiting for verification state', function () {
    Volt::test('join')
        ->set('name', 'Professor Robert van Batavia')
        ->set('email', 'robert@batavia.org')
        ->set('password', 'secret123')
        ->set('interest', 'cagar-budaya')
        ->call('registerMember')
        ->assertSet('isRegistered', true)
        ->assertSee('Pendaftaran Berhasil!')
        ->assertSee('Menunggu Verifikasi')
        ->assertSee('Professor Robert van Batavia');

    // Assert user exists in database
    $user = User::where('email', 'robert@batavia.org')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Professor Robert van Batavia');
    expect($user->reason_for_joining)->toBe('cagar-budaya');
    expect($user->verified)->toBe(0);
});
