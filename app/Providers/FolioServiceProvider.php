<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Laravel\Folio\Folio;
use Livewire\Volt\Volt;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $themePagesPath = $this->resolveThemePagesPath();

        if (! $themePagesPath) {
            return;
        }

        if (! in_array($themePagesPath, Folio::paths(), true)) {
            Folio::path($themePagesPath)->middleware([
                '*' => [
                    //
                ],
            ]);
        }

        $themePath = dirname($themePagesPath);

        $this->loadViewsFrom($themePath, 'theme');

        if (File::isDirectory($themePath.'/components/elements')) {
            Blade::anonymousComponentPath($themePath.'/components/elements');
        }

        if (File::isDirectory($themePath.'/components')) {
            Blade::anonymousComponentPath($themePath.'/components');
        }

        $mountedVoltPaths = collect(Volt::paths())
            ->map(fn ($mountedDirectory) => $mountedDirectory->path ?? null)
            ->filter()
            ->all();

        if (! in_array($themePagesPath, $mountedVoltPaths, true)) {
            Volt::mount($themePagesPath);
        }
    }

    protected function resolveThemePagesPath(): ?string
    {
        $themeJsonPath = base_path('theme.json');
        $defaultThemeName = 'anchor';
        $candidateThemeNames = [];

        if (File::exists($themeJsonPath)) {
            $themeConfig = json_decode((string) File::get($themeJsonPath), true);

            if (is_array($themeConfig) && is_string($themeConfig['name'] ?? null) && $themeConfig['name'] !== '') {
                $candidateThemeNames[] = $themeConfig['name'];
            }
        }

        $candidateThemeNames[] = $defaultThemeName;
        $candidateThemeNames = array_values(array_unique($candidateThemeNames));

        foreach ($candidateThemeNames as $themeName) {
            $themePagesPath = resource_path("themes/{$themeName}/pages");

            if (File::isDirectory($themePagesPath)) {
                return $themePagesPath;
            }
        }

        $themesRoot = resource_path('themes');

        if (! File::isDirectory($themesRoot)) {
            return null;
        }

        foreach ((array) File::directories($themesRoot) as $themeDirectory) {
            $themePagesPath = $themeDirectory.'/pages';

            if (File::isDirectory($themePagesPath)) {
                return $themePagesPath;
            }
        }

        return null;
    }
}
