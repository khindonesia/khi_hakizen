<?php

use Laravel\Folio\Folio;
use Livewire\Volt\MountedDirectory;
use Livewire\Volt\Volt;

it('mounts folio and volt paths for the active theme pages directory', function () {
    $expectedPath = resource_path('themes/anchor/pages');

    expect(Folio::paths())->toContain($expectedPath);

    $voltPaths = collect(Volt::paths())
        ->map(function (MountedDirectory $path) {
            return $path->path;
        })
        ->filter()
        ->values()
        ->all();

    expect($voltPaths)->toContain($expectedPath);
});
