<?php
use App\Models\Aspirasi;
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
name('dashboard.historialita');

new class extends Component implements HasForms, Tables\Contracts\HasTable {
    use InteractsWithForms, Tables\Concerns\InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(\Wave\Post::query()->where('author_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->columns([Tables\Columns\TextColumn::make('user.name')->label('User')->sortable(), Tables\Columns\TextColumn::make('category.name')->label('Category')->searchable()->sortable(), Tables\Columns\TextColumn::make('title')->label('Title')->searchable(), Tables\Columns\ImageColumn::make('image')->label('Image'), Tables\Columns\TextColumn::make('status')->label('Status'), Tables\Columns\IconColumn::make('featured')->label('Featured')->boolean(), Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])
            ->filters([
                // Category filters
            ])
            ->actions([Tables\Actions\EditAction::make()->url(fn(\Wave\Post $record): string => "/dashboard/historialita/{$record->id}/edit"), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
            ->emptyStateHeading('Belum ada postingan ditemukan')
            ->emptyStateDescription('Anda belum membagikan postingan.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([Tables\Actions\Action::make('create')->label('Tulis Postingan Baru')->url('/dashboard/historialita/create')->icon('heroicon-o-plus')->button()]);
    }
};
?>

<x-layouts.app>
    @volt('dashboard.historialita')
        <x-app.container>
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-5 gap-4">
                <div>
                    <x-app.heading title="Postingan Saya" description="Kelola gagasan, artikel, dan usulan cagar budaya Anda"
                        :border="false" />
                    <p class="text-sm text-gray-500 mt-1">Buat, sunting, dan publikasikan aspirasi Anda di platform komunitas
                    </p>
                </div>
                <x-button tag="a" href="/dashboard/historialita/create"
                    class="flex items-center gap-x-2 self-start flex-nowrap bg-[#df1c24] hover:bg-[#c41219] text-white">
                    Tulis Postingan Baru
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
