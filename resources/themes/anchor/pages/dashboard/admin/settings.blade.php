<?php

use Wave\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use function Laravel\Folio\{middleware, name};

middleware(['auth', function ($request, $next) {
    if (! auth()->user()->hasRole('admin')) {
        abort(403);
    }
    return $next($request);
}]);
name('dashboard.admin.settings');

new class extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all();
        $state = [];
        foreach ($settings as $setting) {
            $state[$setting->key] = $setting->value;
        }
        $this->form->fill($state);
    }

    public function form(Form $form): Form
    {
        // Fetch all settings and sort them to keep consistent order
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $groups = $settings->groupBy('group');

        $tabs = [];
        foreach ($groups as $groupName => $groupSettings) {
            $fields = [];
            foreach ($groupSettings as $setting) {
                // Format the label nicely by converting snake_case and dots to Space Cased
                $label = ucwords(str_replace(['site.', 'socmed_', '_', '.'], ' ', $setting->key));

                $field = match ($setting->type) {
                    'text' => Forms\Components\TextInput::make($setting->key)
                        ->label($label)
                        ->maxLength(191),
                    'textarea' => Forms\Components\Textarea::make($setting->key)
                        ->label($label)
                        ->rows(3),
                    'rich_text' => Forms\Components\RichEditor::make($setting->key)
                        ->label($label)
                        ->columnSpanFull(),
                    'image' => Forms\Components\FileUpload::make($setting->key)
                        ->label($label)
                        ->image()
                        ->disk('public')
                        ->directory('settings')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'])
                        ->maxSize(2048),
                    'url' => Forms\Components\TextInput::make($setting->key)
                        ->label($label)
                        ->url()
                        ->maxLength(191),
                    default => Forms\Components\TextInput::make($setting->key)
                        ->label($label),
                };

                $fields[] = $field;
            }

            $tabs[] = Forms\Components\Tabs\Tab::make($groupName)
                ->schema($fields)
                ->columns(2);
        }

        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings Tabs')
                    ->tabs($tabs)
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $formData = $this->form->getState();
        $flatData = \Illuminate\Support\Arr::dot($formData);

        foreach ($flatData as $key => $newValue) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                if ($setting->type === 'image') {
                    $oldValue = $setting->value;
                    if ($oldValue !== $newValue) {
                        // Handle public storage path deletion of old file
                        if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                            Storage::disk('public')->delete($oldValue);
                        }
                    }
                }
                $setting->update(['value' => $newValue]);
            }
        }

        // Invalidate the cache to reflect changes instantly
        Cache::forget('wave_settings');

        Notification::make()
            ->success()
            ->title('Pengaturan berhasil disimpan!')
            ->send();
    }
}
?>

<x-layouts.app>
    @volt('dashboard.admin.settings')
        <x-app.container>
            <div class="mb-6">
                <x-app.heading title="Pengaturan Situs" description="Kelola teks, gambar, media sosial, dan halaman statis secara dinamis" :border="false" />
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-zinc-200/80 dark:border-zinc-800 p-6">
                <form wire:submit="save" class="space-y-6">
                    {{ $this->form }}

                    <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <x-button type="submit" class="bg-[#df1c24] hover:bg-[#c41219] text-white">
                            Simpan Perubahan
                        </x-button>
                    </div>
                </form>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>
