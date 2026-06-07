<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

abstract class BaseManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('setting.view') ?? false;
    }

    public function mount(): void
    {
        $settings = Setting::all();
        $data = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            if ($setting->type === 'json' || $setting->type === 'array') {
                $value = json_decode($value, true) ?: [];
            } elseif (is_string($value) && (str_starts_with($value, '[') || str_starts_with($value, '{'))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
            }
            $data[$setting->key] = $value;
        }
        $this->form->fill(Arr::undot($data));
    }

    public function save(): void
    {
        if (!auth()->user()?->can('setting.update')) {
            abort(403);
        }

        $state = $this->form->getState();
        $flatState = $this->flattenState($state);

        foreach ($flatState as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                if ($setting->type === 'image') {
                    $newValue = is_array($value) ? reset($value) : $value;

                    // If image changed, delete the old file
                    if ($setting->value && $setting->value !== $newValue) {
                        if (Storage::disk('public')->exists($setting->value)) {
                            Storage::disk('public')->delete($setting->value);
                        }
                    }
                    $setting->update(['value' => $newValue]);
                } elseif ($setting->type === 'json' || $setting->type === 'array' || is_array($value)) {
                    $setting->update(['value' => json_encode($value)]);
                } else {
                    $setting->update(['value' => $value]);
                }
            }
        }

        Cache::forget('wave_settings');

        \Filament\Notifications\Notification::make()
            ->title('Settings Saved')
            ->body('Settings have been updated successfully.')
            ->success()
            ->send();
    }

    /**
     * Flatten settings form state preserving indexed arrays/repeaters.
     */
    protected function flattenState(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;

            $settingExists = Setting::where('key', $fullKey)->exists();

            if ($settingExists) {
                $result[$fullKey] = $value;
            } elseif (is_array($value) && !array_is_list($value)) {
                $result = array_merge($result, $this->flattenState($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }
        return $result;
    }
}
