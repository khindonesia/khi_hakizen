<?php
use App\Models\Aspirasi;
use function Laravel\Folio\name;

name('aspirasi.detail');
?>

@php
    $slug = $slug ?? request()->route('slug') ?? request()->route()?->parameter('slug');
    $aspirasi = null;

    if (filled($slug)) {
        $aspirasi = Aspirasi::query()
            ->with(['user', 'category'])
            ->where('status', 'PUBLISHED')
            ->where('slug', $slug)
            ->first();

        if (! app()->runningInConsole()) {
            abort_unless($aspirasi, 404);
        }
    }

    $coverImage = $aspirasi?->image() ?: url('/og_image.png');
    $categoryLabel = $aspirasi?->category?->name ?? 'Aspirasi';
    $authorName = $aspirasi?->user?->name ?? 'Anggota KHI';
    $publishedText = $aspirasi ? Carbon\Carbon::parse($aspirasi->created_at)->format('d M Y') : '';
    $updatedText = $aspirasi ? Carbon\Carbon::parse($aspirasi->updated_at)->format('d M Y') : '';
    $summary = $aspirasi
        ? ($aspirasi->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($aspirasi->body), 180))
        : '';
@endphp

<x-layouts.marketing :seo="[
    'title' => ($aspirasi?->title ?? 'Aspirasi') . ' - Komunitas Historia Indonesia',
    'description' => $summary,
    'image' => $coverImage,
    'type' => 'article',
]">
@if ($aspirasi)
        @include('theme::partials.blog.article-detail', [
            'backHref' => route('aspirasi'),
            'backText' => 'Back to Aspirasi',
            'categoryLabel' => $categoryLabel,
            'title' => $aspirasi->title,
            'coverImage' => $coverImage,
            'authorName' => $authorName,
            'publishedText' => $publishedText,
            'updatedText' => $updatedText,
            'body' => $aspirasi->body,
            'summary' => $summary,
            'sidebarTitle' => 'Detail Aspirasi',
            'sidebarDescription' => 'Ringkasan gagasan, kategori, dan metadata aspirasi komunitas.',
        ])
    @endif

</x-layouts.marketing>
