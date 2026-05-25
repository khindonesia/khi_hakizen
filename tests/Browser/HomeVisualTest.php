<?php

use Laravel\Dusk\Browser;

test('home page matches the Historialita editorial layout', function (): void {
    $this->browse(function (Browser $browser): void {
        $browser->visit('/')
            ->assertSee('Komunitas Historia Indonesia: Penjaga Memori Kolektif Bangsa')
            ->assertSee('Prestasi & Penghargaan')
            ->assertSee('Historia News')
            ->assertSee('Support KHI')
            ->screenshot('home-historialita');
    });
});
