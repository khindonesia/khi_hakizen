<?php
use Wave\Post;
use Wave\Category;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('post');

new class extends Component implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms, Tables\Concerns\InteractsWithTable;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->where('author_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Sama dengan filter di PostResource
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Post $record): string => "/post/{$record->id}/edit"),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No posts found')
            ->emptyStateDescription('You haven\'t created any posts yet')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create New Post')
                    ->url('/post/create')
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
?>

<x-layouts.app>
    @volt('post')
        <x-app.container>
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-5 gap-4">
                <div>
                    <x-app.heading title="My Posts" description="Manage your blog posts" :border="false" />
                    <p class="text-sm text-gray-500 mt-1">Create, edit, and manage your content</p>
                </div>
                <x-button tag="a" href="/post/create" class="flex items-center gap-x-2 self-start">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create New Post
                </x-button>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    {{ $this->table }}
                </div>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>