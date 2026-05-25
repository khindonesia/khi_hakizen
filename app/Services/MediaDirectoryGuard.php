<?php

namespace App\Services;

class MediaDirectoryGuard
{
    /**
     * @param array<int, string> $allowedDisks
     */
    public function normalizeDisk(?string $disk, array $allowedDisks = ['public']): string
    {
        $disk = $disk ?: 'public';

        abort_unless(in_array($disk, $allowedDisks, true), 403);
        abort_unless(config("filesystems.disks.{$disk}.driver") === 'local', 403);

        return $disk;
    }

    public function normalizeDirectory(string $disk, ?string $path): string
    {
        $path = str_replace('\\', '/', (string) $path);
        $path = trim($path, '/');

        abort_if($path !== '' && collect(explode('/', $path))->contains(fn (string $segment): bool => $segment === '..'), 403);
        abort_if(str_contains($path, "\0"), 403);

        $absolutePath = $this->absolutePath($disk, $path);
        $root = $this->storageRoot($disk);
        $realPath = realpath($absolutePath);

        abort_unless($realPath && is_dir($realPath) && $this->isWithinStorageRoot($realPath, $root), 403);

        return $path;
    }

    public function absolutePath(string $disk, string $path): string
    {
        return $this->storageRoot($disk) . ($path === '' ? '' : DIRECTORY_SEPARATOR . $path);
    }

    private function storageRoot(string $disk): string
    {
        $root = config("filesystems.disks.{$disk}.root");
        $realRoot = is_string($root) ? realpath($root) : false;

        abort_unless($realRoot, 403);

        return rtrim($realRoot, DIRECTORY_SEPARATOR);
    }

    private function isWithinStorageRoot(string $realPath, string $root): bool
    {
        $realPath = rtrim($realPath, DIRECTORY_SEPARATOR);

        return $realPath === $root || str_starts_with($realPath, $root . DIRECTORY_SEPARATOR);
    }
}
