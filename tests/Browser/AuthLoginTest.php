<?php

use Laravel\Dusk\Browser;

test('auth login can reveal password and submit credentials', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/auth/login')
            ->type('@email-input', 'admin@admin.com')
            ->click('@submit-button')
            ->waitFor('@password-input')
            ->type('@password-input', 'password')
            ->click('@submit-button')
            ->waitForLocation('/')
            ->assertPathIs('/');
    });
});
