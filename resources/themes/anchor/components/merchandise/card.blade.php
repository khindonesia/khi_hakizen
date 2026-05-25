@props(['product'])

@php
    $normalizeImageUrl = static fn (?string $path) => $path ? (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) ? $path : \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'))) : null;

    $productImage = null;
    $variantImage = $product->defaultVariant?->image_url;

    if ($variantImage) {
        $productImage = $normalizeImageUrl($variantImage);
    } else {
        $productImage = $normalizeImageUrl($product->images->sortBy('sort_order')->first()?->image_url);
    }

    $price = 'Coming soon';
    if ($product->defaultVariant) {
        $price = 'Rp ' . number_format($product->defaultVariant->price, 0, ',', '.');
    }
@endphp

<article class="bg-white border border-[#E9E9E8] rounded-xl overflow-hidden group hover:shadow-md transition-shadow duration-300 flex flex-col h-full">
    <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate class="aspect-square bg-[#e7e0eb]/30 relative overflow-hidden flex items-center justify-center p-8">
        @if ($productImage)
            <img alt="{{ $product->name }}" 
                 class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500" 
                 src="{{ $productImage }}">
        @else
            <div class="text-[#979A9B] flex flex-col items-center">
                <span class="material-symbols-outlined text-5xl mb-2">image</span>
                <span class="text-xs uppercase tracking-wider font-semibold">No Image Available</span>
            </div>
        @endif

        @if ($product->slug === 'khi-official-t-shirt')
            <span class="absolute top-3 right-3 bg-[#EBF9F4] text-[#37352F] text-[13px] font-semibold px-2.5 py-0.5 rounded border border-[#E9E9E8]">Best Seller</span>
        @endif
    </a>
    
    <div class="p-6 flex flex-col flex-1 gap-2">
        <div class="flex justify-between items-start gap-4">
            <h2 class="text-[22px] leading-[1.3] font-semibold text-[#37352F] hover:text-[#df1c24] transition-colors">
                <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate>{{ $product->name }}</a>
            </h2>
            <span class="text-[22px] leading-[1.3] font-semibold text-[#df1c24] whitespace-nowrap">{{ $price }}</span>
        </div>
        <p class="text-base leading-[1.55] text-[#575e75] line-clamp-2 mt-1">
            {{ \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 110) }}
        </p>
        
        <div class="mt-auto pt-4">
            <a href="{{ url('/merchandise/' . $product->slug) }}" wire:navigate 
               class="w-full bg-[#df1c24] text-white font-semibold text-sm rounded-lg py-3 hover:opacity-95 transition-opacity flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
                Add to Cart
            </a>
        </div>
    </div>
</article>
