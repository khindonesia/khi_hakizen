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
use function Laravel\Folio\{middleware, name, parameters};

middleware('auth');
name('manage-historia-news.edit');
parameters(['post']);

new class extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post;
        $this->form->fill([
            'title' => $post->title,
            'slug' => $post->slug,
            'seo_title' => $post->seo_title,
            'meta_keywords' => $post->meta_keywords,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'meta_description' => $post->meta_description,
            'image' => $post->image,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                ->required(),

            TextInput::make('slug')
                ->required()
                ->unique(Post::class, 'slug', ignoreRecord: $this->post),

            TextInput::make('seo_title')->required(),
            TextInput::make('meta_keywords')->required(),
            Textarea::make('excerpt')->required(),
            RichEditor::make('body')->required()->columnSpanFull(),
            Textarea::make('meta_description')->required(),
            FileUpload::make('image')->image(),
        ];
    }

    public function submit()
    {
        $data = $this->form->getState();

        $this->post->update($data);

        return redirect('/manage-historia-news');
    }
}
?>

<x-layouts.app>
    @volt('historia-news.edit')
        <x-app.container>
            <div class="mb-5">
                <x-app.heading title="Edit Post" description="Update your post's content" :border="false" />
            </div>

            <form wire:submit.prevent="submit">
                {{ $this->form }}
                <div class="mt-4 flex gap-3">
                    <x-button type="submit">Update Post</x-button>
                    <x-button tag="a" href="/manage-historia-news" color="gray">Cancel</x-button>
                </div>
            </form>
        </x-app.container>
    @endvolt
</x-layouts.app>
