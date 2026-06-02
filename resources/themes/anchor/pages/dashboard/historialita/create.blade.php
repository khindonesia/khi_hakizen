<?php

use App\Models\Post; // Sesuaikan dengan Model Anda
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
name('dashboard.posts.create');

new class extends Component implements HasForms {
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        // Inisialisasi form dengan array kosong agar state tracking aktif semenjak awal render
        $this->form->fill();
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

                                Forms\Components\TextInput::make('slug')->required()->unique(Post::class, 'slug')->maxLength(191),

                                // PERBAIKAN: Tambahkan live() agar setiap ketikan di RichEditor langsung disinkronisasikan ke Livewire State
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

    public function save(string $statusType): void
    {
        // Mengambil state form terbaru
        $data = $this->form->getState();

        // Injeksi data otomatis backend
        $data['status'] = $statusType;
        $data['author_id'] = auth()->id();

        $defaultCategory = Category::first();
        $data['category_id'] = $defaultCategory ? $defaultCategory->id : 1;

        try {
            Post::create($data);

            $message = $statusType === 'PENDING' ? 'Postingan berhasil diajukan! Menunggu moderasi admin.' : 'Draf berhasil disimpan.';

            Notification::make()->success()->title($message)->send();

            $this->redirect('/dashboard/historialita');
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Gagal menyimpan data')->body($e->getMessage())->send();
        }
    }

    public function cancelCreate(): void
    {
        $this->redirect('/dashboard/historialita');
    }
};
?>

<x-layouts.app>
    {{-- @filamentStyles --}}

    @volt('dashboard.historialita.create')
        <x-app.container>
            <div class="mb-6">
                <div class="flex flex-col gap-y-4 sm:flex-row sm:items-center sm:justify-between">
                    <x-app.heading title="Tulis Postingan Baru"
                        description="Bagikan postingan Anda untuk ditinjau oleh tim admin." :border="false" />

                    <div>
                        <x-button color="gray" tag="button" wire:click="cancelCreate" class="flex items-center gap-x-2">
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
                        <x-button color="gray" type="button" wire:click="cancelCreate">
                            Batal
                        </x-button>
                    </div>

                    <div class="flex items-center gap-x-2">
                        <x-button type="button" color="gray" wire:click="save('DRAFT')" class="border-zinc-300">
                            Simpan sebagai Draft
                        </x-button>

                        <x-button type="button" wire:click="save('PENDING')"
                            class="bg-[#df1c24] hover:bg-[#c41219] text-white shadow-sm">
                            Ajukan ke Admin (Submit)
                        </x-button>
                    </div>
                </div>
            </div>
        </x-app.container>
    @endvolt

    @filamentScripts
</x-layouts.app>
