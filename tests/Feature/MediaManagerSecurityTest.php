<?php

use App\Filament\Pages\Media;
use App\Services\MediaDirectoryGuard;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('rejects media manager traversal paths', function (): void {
    $root = storage_path('framework/testing/media-guard');

    File::ensureDirectoryExists($root . '/safe');
    config()->set('filesystems.disks.public.root', $root);

    $guard = app(MediaDirectoryGuard::class);

    expect($guard->normalizeDisk('public'))->toBe('public')
        ->and($guard->normalizeDirectory('public', 'safe'))->toBe('safe');

    expect(fn () => $guard->normalizeDirectory('public', '../'))->toThrow(HttpException::class);
});

it('rejects unsupported media manager disks', function (): void {
    expect(fn () => app(MediaDirectoryGuard::class)->normalizeDisk('local'))->toThrow(HttpException::class);
});

it('uses the hardened Filament media manager view', function (): void {
    $view = new ReflectionProperty(Media::class, 'view');

    expect($view->getValue())->toBe('filament.pages.media');
});
