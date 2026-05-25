<?php
use App\Actions\PublishAspirasiAction;
use App\Models\Aspirasi;
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
name('dashboard.aspirasi.create');

new class extends Component implements HasForms
{
    use InteractsWithForms;
	    
	    public ?array $data = [];
	    
	    public function form(Form $form): Form
	    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul Aspirasi')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state) . '-' . uniqid()))
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(Aspirasi::class, 'slug')
                    ->maxLength(191),
                Forms\Components\RichEditor::make('body')
                    ->label('Isi Gagasan / Aspirasi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('excerpt')
                    ->label('Ringkasan Singkat')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->label('Foto Pendukung / Cover Image')
                    ->image(),
	                Forms\Components\TextInput::make('seo_title')
	                    ->label('SEO Title')
	                    ->maxLength(191),
	                Forms\Components\Select::make('category_id')
	                    ->label('Kategori')
	                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('meta_description')
                    ->label('SEO Meta Description')
                    ->columnSpanFull(),
	                Forms\Components\Textarea::make('meta_keywords')
	                    ->label('SEO Meta Keywords')
	                    ->columnSpanFull(),
	            ])
            ->columns(2)
            ->statePath('data');
    }
    
    public function create(): void
    {
	        $data = $this->form->getState();
	        
	        try {
	            app(PublishAspirasiAction::class)->create(auth()->user(), $data);
            
            Notification::make()
                ->success()
                ->title('Aspirasi berhasil diterbitkan!')
                ->send();
                
            $this->redirect('/dashboard/aspirasi');
            
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Gagal menerbitkan aspirasi')
                ->body($e->getMessage())
                ->send();
        }
    }
    
    public function cancelCreate(): void
    {
        $this->redirect('/dashboard/aspirasi');
    }
}
?>

<x-layouts.app>
    @volt('dashboard.aspirasi.create')
        <x-app.container>
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <x-app.heading title="Tulis Aspirasi Baru" description="Bagikan gagasan, opini, dan usulan cagar budaya Anda" :border="false" />
                    
                    <div class="flex items-center gap-x-2">
                        <x-button color="gray" tag="button" wire:click="cancelCreate" class="flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Kembali ke Aspirasi Saya
                        </x-button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden p-6">
                <form wire:submit="create" class="space-y-6">
                    {{ $this->form }}
                    
                    <div class="flex justify-end space-x-2 pt-4">
                        <x-button color="gray" type="button" wire:click="cancelCreate">
                            Batal
                        </x-button>
                        <x-button type="submit" class="bg-[#df1c24] hover:bg-[#c41219] text-white">
                            Terbitkan Aspirasi
                        </x-button>
                    </div>
                </form>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>
