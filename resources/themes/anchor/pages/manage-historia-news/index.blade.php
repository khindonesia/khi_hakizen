<?php
use App\Models\Post;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('manage-historia-news');

new class extends Component implements HasForms, Tables\Contracts\HasTable
{
    use InteractsWithForms, Tables\Concerns\InteractsWithTable;

    public ?array $data = [];

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->where('author_id', auth()->id()))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seo_title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('excerpt')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn (Post $record) => "/manage-historia-news/{$record->id}/edit")
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                
                DeleteAction::make(),
            ]);
    }
}
?>


<x-layouts.app>
    @volt('historia-news')
        <x-app.container>
            <div class="flex items-center justify-between mb-5">
                <x-app.heading title="Historia News" description="Check out your posts below" :border="false" />
                <x-button tag="a" href="/manage-historia-news/create">New Post</x-button>
            </div>
            <div class="overflow-x-auto border rounded-lg">
                {{ $this->table }}
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>
