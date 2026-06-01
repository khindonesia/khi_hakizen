<?php
use function Laravel\Folio\{name, render};
use Illuminate\View\View;

name('blog.post');

render(function (View $view, \Wave\Category $category, \Wave\Post $post) {
    if ((int) $post->category_id !== (int) $category->id) {
        abort(404);
    }

    if ($post->status !== 'PUBLISHED') {
        abort(404);
    }

    return $view;
});
?>

@php
    $coverImage = $post->image() ?: url('/og_image.png');
    $categoryLabel = $post->category?->name ?? 'Historia News';
    $authorName = $post->user?->name ?? 'Admin KHI';
    $publishedText = Carbon\Carbon::parse($post->created_at)->format('d M Y');
    $updatedText = Carbon\Carbon::parse($post->updated_at)->format('d M Y');
    $summary = \Illuminate\Support\Str::limit(strip_tags($post->body), 180);
@endphp

<x-layouts.marketing :seo="[
    'title' => $post->title . ' - Komunitas Historia Indonesia',
    'description' => $summary,
    'image' => $coverImage,
    'type' => 'article',
]">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
        </style>
    @endpush

    @include('theme::partials.blog.article-detail', [
        'backHref' => url($post->category->slug),
        'backText' => 'Back to ' . $post->category->name,
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

</x-layouts.marketing>
