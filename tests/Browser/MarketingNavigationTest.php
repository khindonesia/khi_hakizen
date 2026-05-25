<?php

use Laravel\Dusk\Browser;

test('marketing navigation routes resolve without errors', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/')
            ->assertSee('Komunitas Historia Indonesia: Penjaga Memori Kolektif Bangsa')
            ->assertSee('Prestasi & Penghargaan')
            ->visit('/historia-news')
            ->assertSee('Historialita')
            ->visit('/privacy-policy')
            ->assertSee('Privacy Policy')
            ->visit('/terms-of-service')
            ->assertSee('Terms of Service')
            ->visit('/')
            ->assertSee('Prestasi & Penghargaan');
    });
});
