<?php
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('manage-historia-news.create');

new class extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public array $post = [];  // Use array instead of model instance

    public function mount(): void
    {
        $this->post = [
            'title' => '',
            'slug' => '',
            'seo_title' => '',
            'meta_keywords' => '',
            'excerpt' => '',
            'body' => '',
            'meta_description' => '',
            'image' => null,
        ];  
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('post.title')  // Note the 'post.' prefix
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('post.slug', Str::slug($state)))
                ->required(),
                
            TextInput::make('post.slug')
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('post.seo_title')->required(),
            TextInput::make('post.meta_keywords')->required(),
            Textarea::make('post.excerpt')->required(),
            RichEditor::make('post.body')->required()->columnSpanFull(),
            Textarea::make('post.meta_description')->required(),
            FileUpload::make('post.image')->image(),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();

        $post = new Post();
        $post->fill($data['post']); // Fill model with the form data
        $post->author_id = auth()->id();
        $post->status = 'DRAFT';
        $post->save();

        return redirect('/manage-historia-news');
    }
}
?>

<x-layouts.app>
    @volt('historia-news.create')
        <x-app.container>
            <div class="mb-5">
                <x-app.heading title="Create New Post" description="Fill in the details to create a new post" :border="false" />
            </div>

            <form wire:submit.prevent="submit">
                {{ $this->form }}
                <div class="mt-4">
                    <x-button type="submit">Save Post</x-button>
                </div>
            </form>
        </x-app.container>
    @endvolt
</x-layouts.app>
