<?php
use App\Models\Post;
use function Laravel\Folio\name;

name('historia-news.detail');
?>

@php
    $slug = $slug ?? (request()->route('slug') ?? request()->route()?->parameter('slug'));
    $post = null;

    if (filled($slug)) {
        $post = Post::query()
            ->with(['user', 'category'])
            ->where('slug', $slug)
            ->whereHas('category', function ($query): void {
                $query->where('slug', 'historia-news');
            })
            ->first();

        if (!app()->runningInConsole()) {
            abort_unless($post, 404);
        }
    }

    $coverImage = $post?->image() ?: url('/og_image.png');
    $categoryLabel = $post?->category?->name ?? 'Historia News';
    $authorName = $post?->user?->name ?? 'Admin KHI';
    $publishedText = $post ? Carbon\Carbon::parse($post->created_at)->format('d M Y') : '';
    $updatedText = $post ? Carbon\Carbon::parse($post->updated_at)->format('d M Y') : '';
    $summary = $post ? \Illuminate\Support\Str::limit(strip_tags($post->body), 180) : '';
@endphp

<x-layouts.marketing :seo="[
    'title' => ($post?->title ?? 'Historia News') . ' - Komunitas Historia Indonesia',
    'description' => $summary,
    'image' => $coverImage,
    'type' => 'article',
]">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
            rel="stylesheet">
        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
        </style>
    @endpush

    @if ($post)
        @include('theme::partials.blog.article-detail', [
            'backHref' => route('historia-news'),
            'backText' => 'Back to Historia News',
            'categoryLabel' => $categoryLabel,
            'title' => $post->title,
            'coverImage' => $coverImage,
            'authorName' => $authorName,
            'publishedText' => $publishedText,
            'updatedText' => $updatedText,
            'body' => $post->body,
            'summary' => $summary,
            'sidebarTitle' => 'Article Details',
            'sidebarDescription' => 'A quick overview of this Historia News article.',
        ])
    @endif

</x-layouts.marketing>
