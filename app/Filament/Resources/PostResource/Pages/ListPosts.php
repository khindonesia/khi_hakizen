<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Wave\Post;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Post'),
        ];
    }

    /**
     * Membuat Baris Tabs Segmentasi di Atas Tabel (Sama seperti All Orders, Pending, dll)
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Posts')
                ->badge(Post::count()),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'DRAFT'))
                ->badge(Post::where('status', 'DRAFT')->count())
                ->badgeColor('danger'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'PENDING'))
                ->badge(Post::where('status', 'PENDING')->count())
                ->badgeColor('warning'),

            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'PUBLISHED'))
                ->badge(Post::where('status', 'PUBLISHED')->count())
                ->badgeColor('success'),
        ];
    }
}
