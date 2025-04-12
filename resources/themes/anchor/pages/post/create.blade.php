<?php
use Wave\Post;
use Wave\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('post.create');

new class extends Component implements HasForms
{
    use InteractsWithForms;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'author_id' => auth()->id(),
            'status' => 'DRAFT',
            'featured' => false,
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(Post::class, 'slug')
                    ->maxLength(191),
                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('excerpt')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\TextInput::make('seo_title')
                    ->maxLength(191),
                Forms\Components\Hidden::make('author_id'),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('meta_description')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('meta_keywords')
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('status')
                    ->default('DRAFT'),
                Forms\Components\Hidden::make('featured')
                    ->default(false),
            ])
            ->columns(2)
            ->statePath('data');
    }
    
    public function create(): void
    {
        $data = $this->form->getState();
        
        try {
            Post::create($data);
            
            Notification::make()
                ->success()
                ->title('Post created successfully')
                ->send();
                
            $this->redirect('/post');
            
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error creating post')
                ->body($e->getMessage())
                ->send();
        }
    }
    
    public function cancelCreate(): void
    {
        $this->redirect('/post');
    }
}
?>

<x-layouts.app>
    @volt('post.create')
        <x-app.container>
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <x-app.heading title="Create New Post" description="Add a new blog post" :border="false" />
                    
                    <div class="flex items-center gap-x-2">
                        <x-button color="gray" tag="button" wire:click="cancelCreate" class="flex items-center gap-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            Back to Posts
                        </x-button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden p-6">
                <form wire:submit="create" class="space-y-6">
                    {{ $this->form }}
                    
                    <div class="flex justify-end space-x-2 pt-4">
                        <x-button color="gray" type="button" wire:click="cancelCreate">
                            Cancel
                        </x-button>
                        <x-button type="submit">
                            Create Post
                        </x-button>
                    </div>
                </form>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>