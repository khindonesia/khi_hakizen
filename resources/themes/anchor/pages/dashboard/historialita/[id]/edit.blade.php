<?php

use Wave\Post; // Atau \Wave\Post jika tidak ada alias
use Wave\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('dashboard.historialita.edit');

new class extends Component implements HasForms {
    use InteractsWithForms;

    // Ubah tipe data properti menjadi murni class Model Anda
    public \Wave\Post $record;
    public ?array $data = [];

    // PERBAIKAN: Terima parameter sebagai $id (atau $post tergantung nama file [id].blade.php Anda)
    public function mount($id): void
    {
        // Cari record secara manual agar pasti ketemu dan tidak null
        $post = \Wave\Post::findOrFail($id);

        // Kebijakan pengamanan: Memastikan hanya pemilik yang bisa mengakses
        if ($post->author_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->record = $post;

        // Mengisi form dengan data dari model Post yang sedang disunting
        $this->form->fill($post->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Kolom Kiri: Konten Utama
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Content')
                            ->schema([
                                Forms\Components\TextInput::make('title')->live(onBlur: true)->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))->required()->maxLength(191),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(
                                        table: \Wave\Post::class,
                                        column: 'slug',
                                        ignorable: $this->record, // <-- Paksa Filament untuk mengabaikan record yang sedang diedit ini
                                    )
                                    ->maxLength(191),

                                Forms\Components\RichEditor::make('body')->required()->live(onBlur: true)->fileAttachmentsDirectory('posts/attachments')->columnSpanFull(),

                                Forms\Components\Textarea::make('excerpt')->rows(3)->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('SEO Metadata')
                            ->collapsible()
                            ->collapsed()
                            ->schema([Forms\Components\TextInput::make('seo_title')->maxLength(191), Forms\Components\Textarea::make('meta_description')->rows(3), Forms\Components\Textarea::make('meta_keywords')->rows(3)]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Kolom Kanan: Pengaturan Media (Sidebar)
                Forms\Components\Group::make()
                    ->schema([Forms\Components\Section::make('Featured Image')->schema([Forms\Components\FileUpload::make('image')->image()->directory('posts/covers')->imageEditor()->maxSize(2048)]), Forms\Components\Section::make('Opsi Tambahan')->schema([Forms\Components\Toggle::make('featured')->onIcon('heroicon-m-star')->offIcon('heroicon-m-x-mark')->onColor('amber')->label('Featured')])])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3)
            ->statePath('data');
    }

    // Fungsi update dan cancelCreate tetap sama seperti sebelumnya...
    public function update(string $statusType): void
    {
        $data = $this->form->getState();
        $data['status'] = $statusType;

        try {
            $this->record->update($data);
            Notification::make()
                ->success()
                ->title($statusType === 'PENDING' ? 'Postingan berhasil diajukan!' : 'Draf berhasil disimpan.')
                ->send();
            $this->redirect('/dashboard/historialita');
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Gagal memperbarui data')->body($e->getMessage())->send();
        }
    }

    public function cancelEdit(): void
    {
        $this->redirect('/dashboard/historialita');
    }
};
?>

<x-layouts.app>
    {{-- @filamentStyles --}}

    @volt('dashboard.historialita.edit')
        <x-app.container>
            <div class="mb-6">
                <div class="flex flex-col gap-y-4 sm:flex-row sm:items-center sm:justify-between">
                    <x-app.heading title="Edit Postingan"
                        description="Perbarui postingan Anda sebelum ditinjau ulang oleh tim admin." :border="false" />

                    <div>
                        <x-button color="gray" tag="button" wire:click="cancelEdit" class="flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            Kembali
                        </x-button>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="fi-form">
                    {{ $this->form }}
                </div>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:justify-between bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl p-4 shadow-sm">
                    <div>
                        <x-button color="gray" type="button" wire:click="cancelEdit">
                            Batal
                        </x-button>
                    </div>

                    <div class="flex items-center gap-x-2">
                        <x-button type="button" color="gray" wire:click="update('DRAFT')" class="border-zinc-300">
                            Simpan sebagai Draft
                        </x-button>

                        <x-button type="button" wire:click="update('PENDING')"
                            class="bg-[#df1c24] hover:bg-[#c41219] text-white shadow-sm">
                            Ajukan Kembali ke Admin (Submit)
                        </x-button>
                    </div>
                </div>
            </div>
        </x-app.container>
    @endvolt

    @filamentScripts
</x-layouts.app>
